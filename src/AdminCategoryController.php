<?php

namespace Larrock\ComponentCategory;

use Breadcrumbs;
use Cache;
use Illuminate\Http\Request;
use Larrock\Core\Component;
use App\Http\Controllers\Controller;
use JsValidator;
use Lang;
use Larrock\ComponentCategory\Facades\LarrockCategory;
use Redirect;
use Session;
use Validator;
use View;

class AdminCategoryController extends Controller
{
    protected $current_user;

    public function __construct()
    {
        $this->config = LarrockCategory::shareConfig();

        \Config::set('breadcrumbs.view', 'larrock::admin.breadcrumb.breadcrumb');
        Breadcrumbs::register('admin.'. LarrockCategory::getName() .'.index', function($breadcrumbs){
            $breadcrumbs->push(LarrockCategory::getTitle(), '/admin/'. LarrockCategory::getName());
        });
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
     * Show the form for editing the specified resource.
     *
     * @param  int           $id
     *
     * @return View
     */
    public function edit($id)
    {
        $data['data'] = LarrockCategory::getModel()->with(['getFiles', 'getImages'])->findOrFail($id);
        $data['app'] = LarrockCategory::tabbable($data['data']);

        $validator = JsValidator::make(Component::_valid_construct(LarrockCategory::getConfig(), 'update', $id));
        View::share('validator', $validator);

        Breadcrumbs::register('admin.category.edit', function($breadcrumbs, $data)
        {
            $breadcrumbs->push($data->component, '/admin/'. $data->component);
            foreach($data->parent_tree as $item){
                $breadcrumbs->push($item->title, '/admin/'. $item->component .'/'. $item->id);
            }
        });

        return view('larrock::admin.admin-builder.edit', $data);
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
        if($data = LarrockCategory::getModel()->find($id)){
            $name = $data->title;
            $data->clearMediaCollection();
            if($data->delete()){
                \Cache::flush();
                LarrockCategory::actionAttach(LarrockCategory::getConfig(), $data, $request);

                Session::push('message.success', Lang::get('larrock::apps.delete.success', ['name' => $name]));
            }else{
                Session::push('message.danger', 'Раздел не удален');
            }
        }else{
            Session::push('message.danger', 'Такого материала больше нет');
        }
        return back()->withInput();
    }
}