<?php

namespace Larrock\ComponentCategory;

use Illuminate\Http\Request;
use Larrock\ComponentCategory\Models\Category;
use Larrock\Core\Component;
use Illuminate\Routing\Controller;
use Lang;
use Larrock\Core\Traits\AdminMethodsDestroy;
use LarrockCategory;
use LarrockCatalog;
use Larrock\Core\Helpers\MessageLarrock;
use Larrock\Core\Traits\AdminMethodsCreate;
use Larrock\Core\Traits\AdminMethodsEdit;
use Larrock\Core\Traits\ShareMethods;
use LarrockFeed;
use Session;
use Validator;

class AdminCategoryController extends Controller
{
    use AdminMethodsEdit, AdminMethodsCreate, AdminMethodsDestroy, ShareMethods;

    protected $current_user;

    public function __construct()
    {
        $this->shareMethods();
        $this->middleware(LarrockCategory::combineAdminMiddlewares());
        $this->config = LarrockCategory::shareConfig();
        \Config::set('breadcrumbs.view', 'larrock::admin.breadcrumb.breadcrumb');
    }

    /**
     * Light store a newly created resource in storage.
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     * @throws \Illuminate\Database\Eloquent\MassAssignmentException
     * @throws \Exception
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
        $data->url = str_slug($request->input('title'));
        $data->user_id = $request->user()->id;

        if((int)$request->input('parent') !== 0){
            if($get_parent = LarrockCategory::getModel()->find($request->input('parent'))->first()){
                $data->level = (int) $get_parent->level +1;
            }
        }else{
            $data->level = 1;
        }

        if(empty($data->parent) || $data->parent === 0){
            $data->parent = NULL;
        }

        //Проверяем уникальность url
        if(LarrockCategory::getModel()->whereUrl($data->url)->first()){
            $data->url = $data->url .'-'. random_int(0,9999);
        }

        if($data->save()){
            \Cache::flush();
            MessageLarrock::success('Раздел '. $request->input('title') .' добавлен');
            return back()->withInput();
        }
        MessageLarrock::danger('Раздел '. $request->input('title') .' не добавлен');
        return back()->withInput();
    }

    /**
     * Update the specified resource in storage.
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     * @throws \Illuminate\Database\Eloquent\MassAssignmentException
     * @throws \Exception
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
            if(\in_array($row->name, $data->getFillable())){
                if($row instanceof \Larrock\Core\Helpers\FormBuilder\FormCheckbox){
                    $data->{$row->name} = $request->input($row->name, NULL);
                }
                if($row instanceof \Larrock\Core\Helpers\FormBuilder\FormDate){
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

            MessageLarrock::success(Lang::get('larrock::apps.update.success', ['name' => $request->input('title')]));
            \Cache::flush();
            return back();
        }
        MessageLarrock::danger(Lang::get('larrock::apps.update.nothing', ['name' => $request->input('title')]));
        return back()->withInput();
    }

    /**
     * Remove id element
     * @param Request $request
     * @param $id
     * @throws \Exception
     */
    protected function destroyElement(Request $request, $id)
    {
        $allowDestroy = TRUE;
        if($data = $this->config->getModel()::find($id)){
            //Проверка на наличие вложенных разделов или прикрепленных материалов
            if( !$request->has('allowDestroy')){
                if(\count($data->get_child) > 0){
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
                $this->destroyTovars($data);
                $this->destroyFeeds($data);
                $this->destroyChilds($data);

                $name = $data->title;
                $data->clearMediaCollection();
                LarrockCategory::removeDataPlugins(LarrockCategory::getConfig(), $data);

                if($data->delete()){
                    \Cache::flush();
                    MessageLarrock::success(Lang::get('larrock::apps.delete.success', ['name' => $name]));
                }else{
                    MessageLarrock::danger('Раздел не удален');
                }
            }
        }else{
            Session::push('message.danger', 'Такого материала больше нет');
        }
    }

    /**
     * Удаление потомков разделов и их связей
     * @param Category $data
     */
    protected function destroyChilds($data)
    {
        if($data->get_child()->count() > 0){
            foreach ($data->get_child()->get() as $child){
                if($child->get_child()->count() > 0){
                    $this->destroyChilds($child);
                }
                $child_name = $child->title;
                $child->clearMediaCollection();
                $this->destroyTovars($child);
                $this->destroyFeeds($child);
                LarrockCategory::removeDataPlugins(LarrockCategory::getConfig(), $child);
                if($child->delete()){
                    MessageLarrock::success(Lang::get('larrock::apps.delete.success', ['name' => $child_name]));
                }
            }
        }
    }

    /**
     * Удаление товаров каталога в удаляемых разделах
     * @param $data
     */
    protected function destroyTovars($data)
    {
        if(file_exists(base_path(). '/vendor/fanamurov/larrock-catalog') && $data->get_tovars()->count() > 0){
            foreach ($data->get_tovars()->get() as $tovar){
                $tovar_name = $tovar->title;
                $tovar->clearMediaCollection();
                LarrockCatalog::removeDataPlugins(LarrockCatalog::getConfig(), $tovar);
                if($tovar->delete()){
                    MessageLarrock::success(Lang::get('larrock::apps.delete.success', ['name' => $tovar_name]));
                }
            }
        }
    }


    /**
     * Удаление матераилов лент ил удаляемых разделов
     * @param $data
     */
    protected function destroyFeeds($data)
    {
        if(file_exists(base_path(). '/vendor/fanamurov/larrock-feed') && $data->get_feed()->count() > 0){
            foreach ($data->get_feed()->get() as $feed){
                $feed_name = $feed->title;
                $feed->clearMediaCollection();
                LarrockFeed::removeDataPlugins(LarrockFeed::getConfig(), $feed);
                if($feed->delete()){
                    MessageLarrock::success(Lang::get('larrock::apps.delete.success', ['name' => $feed_name]));
                }
            }
        }
    }
}