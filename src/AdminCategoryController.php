<?php

namespace Larrock\ComponentCategory;

use Cache;
use Illuminate\Http\Request;
use Larrock\Core\Component;
use Illuminate\Routing\Controller;
use Lang;
use Larrock\ComponentCategory\Facades\LarrockCategory;
use Larrock\ComponentCatalog\Facades\LarrockCatalog;
use Larrock\Core\Traits\AdminMethodsEdit;
use Larrock\Core\Traits\ShareMethods;
use LarrockFeed;
use Redirect;
use Session;
use Validator;

class AdminCategoryController extends Controller
{
    use AdminMethodsEdit, ShareMethods;

    protected $current_user;

    public function __construct()
    {
        $this->shareMethods();
        $this->middleware(LarrockCategory::combineAdminMiddlewares());
        $this->config = LarrockCategory::shareConfig();
        \Config::set('breadcrumbs.view', 'larrock::admin.breadcrumb.breadcrumb');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param Request                     $request
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        if( !$category = LarrockCategory::getModel()->whereType('feed')->first()){
            LarrockCategory::create(['title' => 'Новый материал', 'url' => str_slug('Новый материал')]);
            $category = LarrockCategory::getModel()->whereType('feed')->first();
        }
        Cache::flush();
        $test = Request::create('/admin/category', 'POST', [
            'title' => 'Новый материал',
            'url' => str_slug('novyy-material'),
            'category' => $request->get('category', $category->id),
            'active' => 0
        ]);
        return $this->store($test);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), LarrockCategory::getValid());
        if($validator->fails()){
            return back()->withInput($request->except('password'))->withErrors($validator);
        }

        $data = LarrockCategory::getModel()->fill($request->all());
        foreach ($this->config->rows as $row){
            if(get_class($row) === 'Larrock\Core\Helpers\FormBuilder\FormCheckbox'){
                $data->{$row->name} = $request->input($row->name, NULL);
            }
            if(get_class($row) === 'Larrock\Core\Helpers\FormBuilder\FormDate'){
                $data->{$row->name} = $request->input('date', date('Y-m-d'));
            }
        }
        $data->level = 0;
        $data->user_id = $request->user()->id;

        if($request->input('parent') !== 0){
            if($get_parent = LarrockCategory::getModel()->find($request->input('parent'))->first()){
                $data->level = (int) $get_parent->level +1;
            }
        }

        //Проверяем уникальность url
        if(LarrockCategory::getModel()->whereUrl($data->url)->first()){
            $data->url = $data->url .'-'. random_int(0,9999);
        }

        if($data->parent === 0){
            $data->parent = NULL;
        }

        if($data->save()){
            Session::push('message.success', 'Материал '. $request->input('title') .' добавлен');
            return Redirect::to('/admin/'. LarrockCategory::getName() .'/'. $data->id .'/edit')->withInput();
        }

        Session::push('message.danger', 'Материал '. $request->input('title') .' не добавлен');
        return back()->withInput();
    }

    /**
     * Light store a newly created resource in storage.
     *
     * @param Request        $request
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function storeEasy(Request $request)
    {
        $validator = Validator::make($request->all(), LarrockCategory::getValid());
        if($validator->fails()){
            return back()->withInput($request->except('password'))->withErrors($validator);
        }

        $data = LarrockCategory::getModel()->fill($request->all());
        $data->active = $request->input('active', 1);
        $data->position = $request->input('position', 0);
        $data->attached = $request->input('attached', 0);
        $data->url = str_slug($request->input('title'));
        $data->user_id = $request->user()->id;

        if((int)$request->input('parent') !== 0){
            if($get_parent = LarrockCategory::getModel()->find($request->input('parent'))->first()){
                $data->level = (int) $get_parent->level +1;
            }
        }else{
            $data->level = 1;
        }

        //Проверяем уникальность url
        if(LarrockCategory::getModel()->whereUrl($data->url)->first()){
            $data->url = $data->url .'-'. random_int(0,9999);
        }

        if($data->parent === 0){
            $data->parent = NULL;
        }

        if($data->save()){
            \Cache::flush();
            Session::push('message.success', 'Раздел '. $request->input('title') .' добавлен');
            return back()->withInput();
        }

        Session::push('message.danger', 'Раздел '. $request->input('title') .' не добавлен');
        return back()->withInput();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int                      $id
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), Component::_valid_construct(LarrockCategory::getConfig(), 'update', $id));
        if($validator->fails()){
            return back()->withInput($request->except('password'))->withErrors($validator);
        }

        $data = LarrockCategory::getModel()->find($id);
        $data->fill($request->all());
        foreach (LarrockCategory::getRows() as $row){
            if(in_array($row->name, $data->getFillable())){
                if(get_class($row) === 'Larrock\Core\Helpers\FormBuilder\FormCheckbox'){
                    $data->{$row->name} = $request->input($row->name, NULL);
                }
                if(get_class($row) === 'Larrock\Core\Helpers\FormBuilder\FormDate'){
                    $data->{$row->name} = $request->input('date', date('Y-m-d'));
                }
            }
        }
        $data->user_id = $request->user()->id;

        if($parent = LarrockCategory::getModel()->whereId($data->parent)->first()){
            $data->level = $parent->level +1;
        }else{
            $data->parent = NULL;
            $data->level = 1;
        }

        if($data->save()){
            LarrockCategory::actionAttach(LarrockCategory::getConfig(), $data, $request);
            LarrockCategory::savePluginSeoData($request);

            Session::push('message.success', Lang::get('larrock::apps.update.success', ['name' => $request->input('title')]));
            \Cache::flush();
            return back();
        }
        Session::push('message.danger', Lang::get('larrock::apps.update.nothing', ['name' => $request->input('title')]));

        return back()->withInput();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param  int $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $allowDestroy = TRUE;

        if($data = LarrockCategory::getModel()->with(['get_child'])->find($id)){
            //Проверка на наличие вложенных разделов или прикрепленных материалов
            if( !$request->has('allowDestroy')){
                if(count($data->get_child) > 0){
                    Session::push('destroyCategory', 'category/'. $id);
                    Session::push('message.dangerDestroy', 'Раздел содержит в себе другие разделы. Удалить их все?');
                    $allowDestroy = NULL;
                }
                if(file_exists(base_path(). '/vendor/fanamurov/larrock-catalog') && $data->get_tovars()->count() > 0){
                    Session::push('destroyCategory', 'category/'. $id);
                    Session::push('message.dangerDestroy', 'Раздел содержит в себе товары каталога. Удалить их все?');
                    $allowDestroy = NULL;
                }
                if(file_exists(base_path(). '/vendor/fanamurov/larrock-feed') && $data->get_feed()->count() > 0){
                    Session::push('destroyCategory', 'category/'. $id);
                    Session::push('message.dangerDestroy', 'Раздел содержит в себе материалы лент. Удалить их все?');
                    $allowDestroy = NULL;
                }
            }

            if($allowDestroy){
                $this->destroyTovars($data, $request);
                $this->destroyFeeds($data, $request);
                $this->destroyChilds($data, $request);

                $name = $data->title;
                $data->clearMediaCollection();
                LarrockCategory::actionAttach(LarrockCategory::getConfig(), $data, $request);

                if($data->delete()){
                    \Cache::flush();

                    Session::push('message.success', Lang::get('larrock::apps.delete.success', ['name' => $name]));
                }else{
                    Session::push('message.danger', 'Раздел не удален');
                }
            }
        }else{
            Session::push('message.danger', 'Такого раздела больше нет');
        }
        return back()->withInput();
    }

    /**
     * Удаление потомков разделов и их связей
     * @param $data
     * @param $request
     */
    protected function destroyChilds($data, $request)
    {
        if($data->get_child()->count() > 0){
            foreach ($data->get_child()->get() as $child){
                if($child->get_child()->count() > 0){
                    $this->destroyChilds($child, $request);
                }
                $child_name = $child->title;
                $child->clearMediaCollection();
                $this->destroyTovars($child, $request);
                $this->destroyFeeds($child, $request);
                LarrockCategory::actionAttach(LarrockCategory::getConfig(), $child, $request);
                if($child->delete()){
                    Session::push('message.success', Lang::get('larrock::apps.delete.success', ['name' => $child_name]));
                }
            }
        }
    }

    /**
     * Удаление товаров каталога в удалеяемых разделах
     *
     * @param $data
     * @param $request
     */
    protected function destroyTovars($data, $request)
    {
        if(file_exists(base_path(). '/vendor/fanamurov/larrock-catalog') && $data->get_tovars()->count() > 0){
            foreach ($data->get_tovars()->get() as $tovar){
                $tovar_name = $tovar->title;
                $tovar->clearMediaCollection();
                LarrockCatalog::actionAttach(LarrockCatalog::getConfig(), $tovar, $request);
                if($tovar->delete()){
                    Session::push('message.success', Lang::get('larrock::apps.delete.success', ['name' => $tovar_name]));
                }
            }
        }
    }


    /**
     * Удаление матераилов лент ил удаляемых разделов
     * @param $data
     * @param $request
     */
    protected function destroyFeeds($data, $request)
    {
        if(file_exists(base_path(). '/vendor/fanamurov/larrock-feed') && $data->get_feed()->count() > 0){
            foreach ($data->get_feed()->get() as $feed){
                $feed_name = $feed->title;
                $feed->clearMediaCollection();
                LarrockFeed::actionAttach(LarrockFeed::getConfig(), $feed, $request);
                if($feed->delete()){
                    Session::push('message.success', Lang::get('larrock::apps.delete.success', ['name' => $feed_name]));
                }
            }
        }
    }
}