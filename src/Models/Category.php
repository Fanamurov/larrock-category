<?php

namespace Larrock\ComponentCategory\Models;

use Cache;
use LarrockFeed;
use LarrockUsers;
use LarrockCatalog;
use LarrockCategory;
use LarrockDiscount;
use Larrock\Core\Component;
use Larrock\Core\Models\Seo;
use Larrock\Core\Traits\GetSeo;
use Larrock\Core\Traits\GetLink;
use Spatie\MediaLibrary\Models\Media;
use Illuminate\Database\Eloquent\Model;
use Larrock\Core\Traits\GetFilesAndImages;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Nicolaslopezj\Searchable\SearchableTrait;
use Larrock\Core\Helpers\Plugins\RenderPlugins;

/**
 * Larrock\ComponentCategory\Models\Category.
 *
 * @property int $id
 * @property string $title
 * @property string $short
 * @property string $description
 * @property string $type
 * @property int $parent
 * @property int $level
 * @property string $url
 * @property int $sitemap
 * @property int $position
 * @property int $active
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCategory\Models\Category find($value)
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCategory\Models\Category whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCategory\Models\Category whereTitle($value)
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCategory\Models\Category whereShort($value)
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCategory\Models\Category whereDescription($value)
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCategory\Models\Category whereType($value)
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCategory\Models\Category whereParent($value)
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCategory\Models\Category whereLevel($value)
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCategory\Models\Category whereUrl($value)
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCategory\Models\Category whereSitemap($value)
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCategory\Models\Category wherePosition($value)
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCategory\Models\Category whereActive($value)
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCategory\Models\Category whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCategory\Models\Category whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCategory\Models\Category type($type)
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCategory\Models\Category level($level)
 * @property string $forecast_url
 * @property string $map
 * @property int $user_id
 * @property int $to_rss
 * @property int $sharing
 * @property int $loads
 * @property-read mixed $full_url
 * @property-read mixed $class_element
 * @property-read mixed $user
 * @property-read mixed $short_wrap
 * @property-read mixed $first_image
 * @property-read mixed $map_coordinate
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\MediaLibrary\Models\Media[] $media
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCategory\Models\Category whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCategory\Models\Category whereToRss($value)
 * @mixin \Eloquent
 * @property int $attached
 * @property mixed $description_render
 * @property mixed $short_render
 * @property mixed $getDiscount
 * @property mixed $getParent
 * @property mixed|string $get_parent_seo_title
 * @property mixed|null $parent_tree_active
 * @property mixed|null $description_category_on_link
 * @property-read mixed $seotitle
 * @property-read mixed $parent_tree
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCategory\Models\Category search($search, $threshold = null, $entireText = false, $entireTextOnly = false)
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCategory\Models\Category searchRestricted($search, $restriction, $threshold = null, $entireText = false, $entireTextOnly = false)
 * @property-read mixed $get_seo_title
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCategory\Models\Category whereComponent($value)
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCategory\Models\Category whereDiscountId($value)
 */
class Category extends Model implements HasMedia
{
    /** @var $this Component */
    protected $config;

    use SearchableTrait, GetFilesAndImages, GetSeo, GetLink;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->fillable(LarrockCategory::addFillableUserRows([]));
        $this->config = LarrockCategory::getConfig();
        $this->table = LarrockCategory::getTable();
    }

    protected $casts = [
        'position' => 'integer',
        'active' => 'integer',
        'sitemap' => 'integer',
        'rss' => 'integer',
        'level' => 'integer',
        'parent' => 'integer',
        'to_rss' => 'integer',
        'sharing' => 'integer',
        'user_id' => 'integer',
    ];

    protected $appends = [
        'full_url',
        'class_element',
        'first_image',
    ];

    // no need for this, but you can define default searchable columns:
    protected $searchable = [
        'columns' => [
            'category.title' => 10,
        ],
    ];

    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @return mixed|string
     */
    public function getGetParentSeoTitleAttribute()
    {
        if ($get_seo = Seo::whereSeoIdConnect($this->parent)->first()) {
            return $get_seo->seo_title;
        }
        if ($get_parent = LarrockCategory::getModel()->whereId($this->parent)->first()) {
            if ($get_seo = Seo::whereSeoUrlConnect($get_parent->url)->first()) {
                return $get_seo->seo_title;
            }

            return $get_parent->title;
        }

        return $this->title;
    }

    public function getDescriptionCategoryOnLinkAttribute()
    {
        if (config('larrock.catalog.DescriptionCatalogCategoryLink')) {
            return LarrockFeed::getModel()->find($this->description_link);
        }

        return null;
    }

    public function getChild()
    {
        return $this->hasMany(LarrockCategory::getModelName(), 'parent', 'id')
            ->orderBy('position', 'DESC')->orderBy('updated_at', 'ASC');
    }

    public function getChildActive()
    {
        return $this->hasMany(LarrockCategory::getModelName(), 'parent', 'id')->whereActive(1)
            ->orderBy('position', 'DESC')->orderBy('updated_at', 'ASC');
    }

    public function getParentTreeAttribute()
    {
        $key = 'tree_categoryAttr'.$this->id;
        $list = Cache::rememberForever($key, function () {
            $list[] = $this;

            return $this->iterateTree($this, $list);
        });

        return $list;
    }

    /**
     * @param Category $category
     * @param array $list
     * @return array
     */
    protected function iterateTree($category, $list = [])
    {
        $cache_key = sha1('iterate_tree'.$category->id);
        $get_data = Cache::rememberForever($cache_key, function () use ($category) {
            if ($parent = $category->getParent()->first()) {
                return $parent;
            }

            return 'empty';
        });
        if ($get_data && $get_data !== 'empty') {
            $list[] = $get_data;

            return $this->iterateTree($get_data, $list);
        }

        return array_reverse($list);
    }

    public function getParentTreeActiveAttribute()
    {
        $key = 'tree_category_active'.$this->id;
        $list = Cache::rememberForever($key, function () {
            $list[] = $this;

            return $this->iterateTreeActive($this, $list);
        });
        if (collect($list)->first()->level !== 1) {
            return null;
        }

        return $list;
    }

    /**
     * @param $category Category
     * @param array $list
     * @return array
     */
    protected function iterateTreeActive($category, $list = [])
    {
        $cache_key = sha1('iterate_treeActive'.$category->id);
        $get_data = Cache::rememberForever($cache_key, function () use ($category) {
            if ($parent = $category->getParentActive()->first()) {
                return $parent;
            }

            return 'empty';
        });

        if ($get_data && $get_data !== 'empty') {
            $list[] = $get_data;

            return $this->iterate_tree_active($get_data, $list);
        }

        return array_reverse($list);
    }

    public function getParent()
    {
        return $this->hasOne(LarrockCategory::getModelName(), 'id', 'parent');
    }

    public function getParentActive()
    {
        return $this->hasOne(LarrockCategory::getModelName(), 'id', 'parent')->whereActive(1);
    }

    public function getFullUrlAttribute()
    {
        return Cache::rememberForever('url_category'.$this->id, function () {
            $url = '';
            if ($this->component) {
                $url = '/'.$this->component;
            }
            foreach ($this->parent_tree as $category) {
                $url .= '/'.$category->url;
            }

            return $url;
        });
    }

    public function getClassElementAttribute()
    {
        return 'category';
    }

    public function getUserAttribute()
    {
        return LarrockUsers::getModel()->whereId($this->user_id)->first();
    }

    public function getGoods()
    {
        return $this->belongsToMany(LarrockCatalog::getModelName(), 'link', 'id_child', 'id_parent')
            ->where('model_parent', '=', LarrockCatalog::getModelName())
            ->where('model_child', '=', LarrockCategory::getModelName())
            ->orderBy('position', 'DESC')->orderBy('updated_at', 'ASC');
    }

    public function getGoodsActive()
    {
        return $this->belongsToMany(LarrockCatalog::getModelName(), 'link', 'id_child', 'id_parent')
            ->where('model_parent', '=', LarrockCatalog::getModelName())
            ->where('model_child', '=', LarrockCategory::getModelName())
            ->whereActive(1)->orderBy('position', 'DESC')->orderBy('cost', 'DESC');
    }

    public function getGoodsCount()
    {
        return $this->belongsToMany(LarrockCatalog::getModelName(), 'link', 'id_child', 'id_parent')
            ->where('model_parent', '=', LarrockCatalog::getModelName())
            ->where('model_child', '=', LarrockCategory::getModelName())->count();
    }

    public function getShortWrapAttribute()
    {
        return mb_strimwidth($this->short, 0, 200, '...');
    }

    public function getFeed()
    {
        return $this->hasMany(LarrockFeed::getModelName(), 'category', 'id')->orderBy('position', 'DESC');
    }

    public function getFeedActive()
    {
        return $this->hasMany(LarrockFeed::getModelName(), 'category', 'id')->whereActive(1)->orderBy('position', 'DESC');
    }

    public function getDiscount()
    {
        return $this->hasOne(LarrockDiscount::getModelName(), 'id', 'discount_id');
    }

    /**
     * Замена тегов плагинов на их данные.
     * @return mixed
     * @throws \Throwable
     */
    public function getShortRenderAttribute()
    {
        $cache_key = 'ShortRender'.$this->table.'-'.$this->id;
        if (\Auth::check()) {
            $cache_key .= '-'.\Auth::user()->role->first()->level;
        }

        return Cache::rememberForever($cache_key, function () {
            $renderPlugins = new RenderPlugins($this->short, $this);
            $render = $renderPlugins->renderBlocks()->renderImageGallery()->renderFilesGallery();

            return $render->rendered_html;
        });
    }

    /**
     * Замена тегов плагинов на их данные.
     * @return mixed
     * @throws \Throwable
     */
    public function getDescriptionRenderAttribute()
    {
        $cache_key = 'DescriptionRender'.$this->table.'-'.$this->id;
        if (\Auth::check()) {
            $cache_key .= '-'.\Auth::user()->role->first()->level;
        }

        return Cache::rememberForever($cache_key, function () {
            $renderPlugins = new RenderPlugins($this->description, $this);
            $render = $renderPlugins->renderBlocks()->renderImageGallery()->renderFilesGallery();

            return $render->rendered_html;
        });
    }

    /**
     * Перезаписываем метод из HasMediaTrait.
     * @param string $collectionName
     * @return mixed
     */
    public function loadMedia(string $collectionName)
    {
        $cache_key = sha1('loadMediaCache'.$collectionName.$this->id.$this->getConfig()->getName());

        return Cache::rememberForever($cache_key, function () use ($collectionName) {
            $collection = $this->exists
                ? $this->media
                : collect($this->unAttachedMediaLibraryItems)->pluck('media');

            return $collection->filter(function (Media $mediaItem) use ($collectionName) {
                if ($collectionName === '') {
                    return true;
                }

                return $mediaItem->collection_name === $collectionName;
            })->sortBy('order_column')->values();
        });
    }
}
