<?php

namespace Larrock\ComponentCategory;

use Cache;
use LarrockCategory;
use Larrock\Core\Component;
use Larrock\Core\Helpers\Tree;
use Larrock\ComponentCategory\Models\Category;
use Larrock\Core\Helpers\FormBuilder\FormInput;
use Larrock\Core\Helpers\FormBuilder\FormHidden;
use Larrock\Core\Helpers\FormBuilder\FormCategory;
use Larrock\Core\Helpers\FormBuilder\FormCheckbox;
use Larrock\Core\Helpers\FormBuilder\FormTextarea;

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
        $this->setRow($row->setConnect(Category::class, 'getCategory')
            ->setMaxItems(1)->setDefaultValue(null)->setFillable());

        $row = new FormInput('title', 'Заголовок');
        $this->setRow($row->setValid('max:255|required')->setTypo()->setFillable());

        $row = new FormTextarea('short', 'Краткое описание');
        $this->setRow($row->setTypo()->setFillable());

        $row = new FormTextarea('description', 'Полное описание');
        $this->setRow($row->setTypo()->setFillable());

        $row = new FormHidden('component', 'Компонент');
        $this->setRow($row->setFillable());

        $row = new FormHidden('level', 'Уровень вложенности раздела');
        $this->setRow($row->setDefaultValue('level')->setFillable());

        $row = new FormCheckbox('sitemap', 'Публиковать ли в sitemap');
        $this->setRow($row->setDefaultValue(1)->setTab('seo', 'Seo')->setFillable());

        $row = new FormCheckbox('rss', 'Публиковать ли в rss');
        $this->setRow($row->setDefaultValue(0)->setTab('seo', 'Seo')->setFillable());

        $row = new FormInput('description_link', 'ID материала Feed для описания');
        $this->setRow($row->setCssClassGroup('uk-width-1-2 uk-width-1-3@m')->setFillable());

        return $this;
    }

    public function createSitemap()
    {
        $tree = new Tree();
        if ($activeCategory = $tree->listActiveCategories(LarrockCategory::getModel()->whereActive(1)->whereSitemap(1)->whereParent(null)->get())) {
            return LarrockCategory::getModel()->whereActive(1)->whereSitemap(1)->whereIn(LarrockCategory::getTable().'.id', $activeCategory)->get();
        }

        return [];
    }

    public function search($admin = null)
    {
        return Cache::rememberForever('search'.$this->name.$admin, function () use ($admin) {
            $data = [];
            if ($admin) {
                $items = LarrockCategory::getModel()->with(['getParent'])->get();
            } else {
                $items = LarrockCategory::getModel()->whereActive(1)->with(['getParentActive'])->get();
            }
            foreach ($items as $item) {
                $data[$item->id]['id'] = $item->id;
                $data[$item->id]['title'] = $item->title;
                $data[$item->id]['full_url'] = $item->full_url;
                $data[$item->id]['component'] = $this->name;
                $data[$item->id]['category'] = null;
                if ($admin) {
                    if ($item->getParent) {
                        $data[$item->id]['category'] = $item->getParent->title;
                    }
                } else {
                    if ($item->getParentActive) {
                        $data[$item->id]['category'] = $item->getParentActive->title;
                    }
                }
            }
            if (\count($data) === 0) {
                return null;
            }

            return $data;
        });
    }
}
