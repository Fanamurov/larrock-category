<?php

namespace Larrock\ComponentCategory;

use Cache;
use Larrock\Core\Component;
use Larrock\Core\Helpers\FormBuilder\FormCategory;
use Larrock\Core\Helpers\FormBuilder\FormCheckbox;
use Larrock\Core\Helpers\FormBuilder\FormHidden;
use Larrock\Core\Helpers\FormBuilder\FormInput;
use Larrock\Core\Helpers\FormBuilder\FormTextarea;
use Larrock\ComponentCategory\Models\Category;
use Larrock\ComponentCategory\Facades\LarrockCategory;
use Larrock\Core\Helpers\Tree;

class CategoryComponent extends Component
{
    public function __construct()
    {
        $this->name = $this->table = 'category';
        $this->title = 'Разделы';
        $this->description = 'Структура сайта';
        $this->model = $this->model = \config('larrock.models.category', Category::class);
        $this->addRows()->addPositionAndActive()->isSearchable()->addPlugins();
    }

    protected function addPlugins()
    {
        $this->addPluginImages()->addPluginFiles()->addPluginSeo();
        return $this;
    }

    protected function addRows()
    {
        $row = new FormCategory('parent', 'Родительский раздел');
        $this->rows['parent'] = $row->setConnect(Category::class, 'get_category')->setMaxItems(1)->setDefaultValue(NULL);

        $row = new FormInput('title', 'Заголовок');
        $this->rows['title'] = $row->setValid('max:255|required')->setTypo();

        $row = new FormTextarea('short', 'Краткое описание');
        $this->rows['short'] = $row->setTypo();

        $row = new FormTextarea('description', 'Полное описание');
        $this->rows['description'] = $row->setTypo();

        $row = new FormHidden('type', 'Тип раздела');
        $this->rows['type'] = $row->setDefaultValue('type');

        $row = new FormHidden('level', 'Уровень вложенности раздела');
        $this->rows['level'] = $row->setDefaultValue('level');

        $row = new FormCheckbox('sitemap', 'Публиковать ли в sitemap');
        $this->rows['sitemap'] = $row->setDefaultValue(1)->setTab('seo', 'Seo');

        $row = new FormCheckbox('rss', 'Публиковать ли в rss');
        $this->rows['rss'] = $row->setDefaultValue(0)->setTab('seo', 'Seo');

        $row = new FormCategory('soputka', 'Сопутствующие разделы');
        $this->rows['soputka'] = $row->setConnect(Category::class, 'get_soputka')->setAttached()->setAllowEmpty();

        $row = new FormInput('description_link', 'ID материала Feed для описания');
        $this->rows['description_link'] = $row->setCssClassGroup('uk-width-1-2 uk-width-medium-1-3 uk-width-large-1-4')->setFillable();

        return $this;
    }

    public function createSitemap()
    {
        $tree = new Tree();
        if($activeCategory = $tree->listActiveCategories(LarrockCategory::getModel()->whereActive(1)->whereSitemap(1)->whereParent(NULL)->get())){
            return LarrockCategory::getModel()->whereActive(1)->whereSitemap(1)->whereIn(LarrockCategory::getConfig()->table .'.id', $activeCategory)->get();
        }
        return [];
    }

    public function search($admin = NULL)
    {
        return Cache::remember('search'. $this->name. $admin, 1440, function() use ($admin){
            $data = [];
            if($admin){
                $items = LarrockCategory::getModel()->with(['get_parent'])->get(['id', 'title', 'parent', 'component', 'url']);
            }else{
                $items = LarrockCategory::getModel()->whereActive(1)->with(['get_parentActive'])->get(['id', 'title', 'parent', 'component', 'url']);
            }
            foreach ($items as $item){
                $data[$item->id]['id'] = $item->id;
                $data[$item->id]['title'] = $item->title;
                $data[$item->id]['full_url'] = $item->full_url;
                $data[$item->id]['component'] = $this->name;
                $data[$item->id]['category'] = NULL;
                if($admin){
                    if($item->get_parent){
                        $data[$item->id]['category'] = $item->get_parent->title;
                    }
                }else{
                    if($item->get_parentActive){
                        $data[$item->id]['category'] = $item->get_parentActive->title;
                    }
                }
            }
            if(count($data) === 0){
                return NULL;
            }
            return $data;
        });
    }
}