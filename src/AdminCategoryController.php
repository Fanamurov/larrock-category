<?php

namespace Larrock\ComponentCategory;

use Alert;
use Breadcrumbs;
use Cache;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use JsValidator;
use Lang;
use Larrock\ComponentCategory\Models\Category;
use Larrock\Core\Component;
use Larrock\Core\Middleware\SaveAdminPluginsData;
use Redirect;
use Validator;
use View;

class AdminCategoryController extends Controller
{
	protected $config;
	protected $current_user;

	public function __construct()
	{
        $Component = new CategoryComponent();
        $this->config = $Component->shareConfig();

        Breadcrumbs::setView('larrock::admin.breadcrumb.breadcrumb');
        Breadcrumbs::register('admin.'. $this->config->name .'.index', function($breadcrumbs){
            $breadcrumbs->push($this->config->title, '/admin/'. $this->config->name);
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
        if( !$category = Category::whereType('feed')->first()){
            Category::create(['title' => 'Новый материал', 'url' => str_slug('Новый материал')]);
            $category = Category::whereType('feed')->first();
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
        $validator = Validator::make($request->all(), $this->config->valid);
        if($validator->fails()){
            return back()->withInput($request->except('password'))->withErrors($validator);
        }

		$data = new Category();
		$data->fill($request->all());
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
			if($get_parent = Category::find($request->input('parent'))->first()){
				$data->level = (int) $get_parent->level +1;
			}
		}

		if($data->save()){
			Alert::add('successAdmin', 'Материал '. $request->input('title') .' добавлен')->flash();
			return Redirect::to('/admin/'. $this->config->name .'/'. $data->id .'/edit')->withInput();
		}

		Alert::add('errorAdmin', 'Материал '. $request->input('title') .' не добавлен')->flash();
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
        $validator = Validator::make($request->all(), $this->config->valid);
        if($validator->fails()){
            return back()->withInput($request->except('password'))->withErrors($validator);
        }

		$data = new Category();
		$data->fill($request->all());
		$data->active = $request->input('active', 1);
		$data->position = $request->input('position', 0);
		$data->attached = $request->input('attached', 0);
		$data->url = str_slug($request->input('title'));
		$data->user_id = $request->user()->id;

		if((int)$request->input('parent') !== 0){
			if($get_parent = Category::find($request->input('parent'))->first()){
				$data->level = (int) $get_parent->level +1;
			}
		}else{
			$data->level = 1;
		}

		if($data->save()){
			\Cache::flush();
			Alert::add('successAdmin', 'Раздел '. $request->input('title') .' добавлен')->flash();
			return back()->withInput();
		}

		Alert::add('errorAdmin', 'Раздел '. $request->input('title') .' не добавлен')->flash();
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
		$data['data'] = Category::with(['getFiles', 'getImages'])->findOrFail($id);
        $data['app'] = $this->config->tabbable($data['data']);

        $validator = JsValidator::make(Component::_valid_construct($this->config, 'update', $id));
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
        $validator = Validator::make($request->all(), Component::_valid_construct($this->config, 'update', $id));
        if($validator->fails()){
            return back()->withInput($request->except('password'))->withErrors($validator);
        }

		$data = Category::find($id);

		$data->fill($request->all());
        foreach ($this->config->rows as $row){
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

		if($parent = Category::whereId($data->parent)->first()){
            $data->level = $parent->level +1;
        }else{
		    $data->parent = NULL;
		    $data->level = 1;
        }

		if($data->save()){
            $Component = new CategoryComponent();
            $Component->actionAttach($this->config, $data, $request);
            $Component->savePluginSeoData($request);

            Alert::add('successAdmin', Lang::get('larrock::apps.update.success', ['name' => $request->input('title')]))->flash();
			\Cache::flush();
			return back();
		}
        Alert::add('warning', Lang::get('larrock::apps.update.nothing', ['name' => $request->input('title')]))->flash();

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
		if($data = Category::find($id)){
            $name = $data->title;
            $data->clearMediaCollection();
            if($data->delete()){
                \Cache::flush();
                $Component = new CategoryComponent();
                $Component->actionAttach($this->config, $data, $request);

                Alert::add('successAdmin', Lang::get('larrock::apps.delete.success', ['name' => $name]))->flash();
            }else{
                Alert::add('errorAdmin', 'Раздел не удален')->flash();
            }
        }else{
            Alert::add('errorAdmin', 'Такого материала больше нет')->flash();
        }
		return back()->withInput();
	}
}