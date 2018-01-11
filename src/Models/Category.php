<?php

namespace Larrock\ComponentCategory\Models;

use Cache;
use Illuminate\Database\Eloquent\Model;
use Larrock\Core\Helpers\Plugins\RenderPlugins;
use Larrock\Core\Traits\GetFilesAndImages;
use Larrock\Core\Traits\GetLink;
use Larrock\Core\Traits\GetSeo;
use Larrock\Core\Component;
use LarrockUsers;
use Nicolaslopezj\Searchable\SearchableTrait;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Spatie\MediaLibrary\HasMedia\Interfaces\HasMediaConversions;
use Larrock\ComponentCategory\Facades\LarrockCategory;
use Larrock\ComponentCatalog\Facades\LarrockCatalog;
use Larrock\ComponentFeed\Facades\LarrockFeed;
use Larrock\ComponentDiscount\Facades\LarrockDiscount;
use Larrock\Core\Models\Seo;

/**
 * Larrock\ComponentCategory\Models\Category
 *
 * @property integer $id
 * @property string $title
 * @property string $short
 * @property string $description
 * @property string $type
 * @property integer $parent
 * @property integer $level
 * @property string $url
 * @property integer $sitemap
 * @property integer $position
 * @property integer $active
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
 * @property integer $user_id
 * @property integer $to_rss
 * @property integer $sharing
 * @property integer $loads
 * @property-read mixed $full_url
 * @property-read mixed $class_element
 * @property-read mixed $user
 * @property-read mixed $short_wrap
 * @property-read mixed $first_image
 * @property-read mixed $map_coordinate
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\MediaLibrary\Media[] $media
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCategory\Models\Category whereForecastUrl($value)
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCategory\Models\Category whereMap($value)
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCategory\Models\Category whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCategory\Models\Category whereToRss($value)
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCategory\Models\Category whereSharing($value)
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCategory\Models\Category whereLoads($value)
 * @mixin \Eloquent
 * @property integer $attached
 * @property-read mixed $seotitle
 * @property-read mixed $parent_tree
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCategory\Models\Category whereAttached($value)
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCategory\Models\Category search($search, $threshold = null, $entireText = false, $entireTextOnly = false)
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCategory\Models\Category searchRestricted($search, $restriction, $threshold = null, $entireText = false, $entireTextOnly = false)
 * @property-read mixed $get_seo_title
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCategory\Models\Category whereComponent($value)
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCategory\Models\Category whereDiscountId($value)
 */
class Category extends Model implements HasMediaConversions
{
    /**
     * @var $this Component
     */
    protected $config;
    
    use SearchableTrait;
    use HasMediaTrait;
    use GetFilesAndImages;
    use GetSeo;
    use GetLink;

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
    ];

    protected $appends = [
        'full_url',
        'class_element',
        'first_image'
    ];

    protected $guarded = ['user_id'];

    // no need for this, but you can define default searchable columns:
    protected $searchable = [
        'columns' => [
            'category.title' => 10
        ]
    ];

    public function getConfig()
    {
        return $this->config;
    }

    public function getGetParentSeoTitleAttribute()
    {
        if($get_seo = Seo::whereSeoIdConnect($this->parent)->first()){
            return $get_seo->seo_title;
        }
        if($get_parent = LarrockCategory::getModel()->whereId($this->parent)->first()){
            if($get_seo = Seo::whereSeoUrlConnect($get_parent->url)->first()){
                return $get_seo->seo_title;
            }
            return $get_parent->title;
        }
        return $this->title;
    }

    public function getDescriptionCategoryOnLinkAttribute()
    {
        if(config('larrock.catalog.DescriptionCatalogCategoryLink')){
            return LarrockFeed::getModel()->find($this->description_link);
        }
        return NULL;
    }

    public function get_child()
    {
        return $this->hasMany(LarrockCategory::getModelName(), 'parent', 'id')
            ->orderBy('position', 'DESC')->orderBy('updated_at', 'ASC');
    }

    public function get_childActive()
    {
        return $this->hasMany(LarrockCategory::getModelName(), 'parent', 'id')->whereActive(1)
            ->orderBy('position', 'DESC')->orderBy('updated_at', 'ASC');
    }

    public function getParentTreeAttribute()
    {
        $key = 'tree_category'. $this->id;
        $list = Cache::remember($key, 1440, function() {
            $list[] = $this;
            return $this->iterate_tree($this, $list);
        });
        return $list;
    }

    /**
     * @param $category Category
     * @param array $list
     * @return array
     */
    protected function iterate_tree($category, $list = [])
    {
        if($get_data = $category->get_parent()->first()){
            $list[] = $get_data;
            return $this->iterate_tree($get_data, $list);
        }
        return array_reverse($list);
    }

    public function getParentTreeActiveAttribute()
    {
        $key = 'tree_category_active'. $this->id;
        $list = Cache::remember($key, 1440, function() {
            $list[] = $this;
            return $this->iterate_tree_active($this, $list);
        });
        return $list;
    }

    /**
     * @param $category Category
     * @param array $list
     * @return array
     */
    protected function iterate_tree_active($category, $list = [])
    {
        if($get_data = $category->get_parentActive()->first()){
            $list[] = $get_data;
            return $this->iterate_tree_active($get_data, $list);
        }
        return array_reverse($list);
    }

    public function get_parent()
    {
        return $this->hasOne(LarrockCategory::getModelName(), 'id', 'parent');
    }

    public function get_parentActive()
    {
        return $this->hasOne(LarrockCategory::getModelName(), 'id', 'parent')->whereActive(1);
    }

    public function getFullUrlAttribute()
    {
        return Cache::remember('url_category'. $this->id, 1440, function() {
            $url = '';
            if($this->component){
                $url = '/'. $this->component;
            }
            foreach ($this->parent_tree as $category){
                $url .= '/'. $category->url;
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

    public function get_tovars()
    {
        return $this->belongsToMany(LarrockCatalog::getModelName(), 'category_catalog', 'category_id', 'catalog_id')
            ->orderBy('position', 'DESC')->orderBy('updated_at', 'ASC');
    }

    public function get_tovarsActive()
    {
        return $this->belongsToMany(LarrockCatalog::getModelName(), 'category_catalog', 'category_id', 'catalog_id')
            ->whereActive(1)->orderBy('position', 'DESC')->orderBy('cost', 'DESC');
    }

    public function get_tovarsCount()
    {
        return $this->belongsToMany(LarrockCatalog::getModelName(), 'category_catalog', 'category_id', 'catalog_id')->count();
    }

    public function getShortWrapAttribute()
    {
        return mb_strimwidth($this->short, 0, 200, '...');
    }

    public function get_feed()
    {
        return $this->hasMany(LarrockFeed::getModelName(), 'category', 'id')->orderBy('position', 'DESC');
    }

    public function get_feedActive()
    {
        return $this->hasMany(LarrockFeed::getModelName(), 'category', 'id')->whereActive(1)->orderBy('position', 'DESC');
    }

    public function get_soputka()
    {
        return $this->belongsToMany(LarrockCategory::getModelName(), 'category_link', 'category_id', 'category_id_link')
            ->orderBy('position', 'DESC');
    }

    public function get_soputkaTovars()
    {
        $find_categories = $this->belongsToMany(LarrockCategory::getModelName(), 'category_link', 'category_id', 'category_id_link')
            ->orderBy('position', 'DESC')->get(['id']);
        $list_categories = [];
        foreach($find_categories as $category){
            $list_categories[] = $category->id;
        }
        return LarrockCatalog::getModel()->whereActive(1)->whereHas('category', function ($q) use ($list_categories){
            $q->whereIn('category.id', $list_categories);
        })->get();
    }

    public function get_discount()
    {
        return $this->hasOne(LarrockDiscount::getModelName(), 'id', 'discount_id');
    }

    /**
     * Замена тегов плагинов на их данные
     *
     * @return mixed
     */
    public function getShortRenderAttribute()
    {
        $cache_key = 'ShortRender'. $this->table.'-'. $this->id;
        if(\Auth::check()){
            $cache_key .= '-'. \Auth::user()->role->first()->level;
        }

        return \Cache::remember($cache_key, 1440, function(){
            $renderPlugins = new RenderPlugins($this->short, $this);
            $render = $renderPlugins->renderBlocks()->renderImageGallery()->renderFilesGallery();
            return $render->rendered_html;
        });
    }

    /**
     * Замена тегов плагинов на их данные
     *
     * @return mixed
     */
    public function getDescriptionRenderAttribute()
    {
        $cache_key = 'DescriptionRender'. $this->table.'-'. $this->id;
        if(\Auth::check()){
            $cache_key .= '-'. \Auth::user()->role->first()->level;
        }

        return \Cache::remember($cache_key, 1440, function(){
            $renderPlugins = new RenderPlugins($this->description, $this);
            $render = $renderPlugins->renderBlocks()->renderImageGallery()->renderFilesGallery();
            return $render->rendered_html;
        });
    }
}