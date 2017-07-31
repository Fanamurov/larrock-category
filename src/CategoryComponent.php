<?php

namespace Larrock\ComponentCategory;

use Larrock\Core\Component;
use Larrock\Core\Helpers\FormBuilder\FormCategory;
use Larrock\Core\Helpers\FormBuilder\FormCheckbox;
use Larrock\Core\Helpers\FormBuilder\FormHidden;
use Larrock\Core\Helpers\FormBuilder\FormInput;
use Larrock\Core\Helpers\FormBuilder\FormTextarea;
use Larrock\ComponentCategory\Models\Category;

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
        $this->rows['parent'] = $row->setValid('required')->setConnect(Category::class, 'get_category')->setMaxItems(1);

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

        //$row = new FormCheckbox('attached', 'Прикреплен на главную');

        return $this;
    }
}