# Laravel Larrock CMS :: Category Component

---

#### Depends
- fanamurov/larrock-core

## INSTALL

1. Install larrock-category
    ```sh
    composer require fanamurov/larrock-category
    ```

2. Add the ServiceProvider to the providers array in app/config/app.php
    ```php
    //LARROCK COMPONENT Category DEPENDS
    \Larrock\ComponentCategory\LarrockComponentCategoryServiceProvider::class,
    ```
  
    aliases
    ```php
    'Category' => \Larrock\ComponentCategory\Facades\LarrockCategory::class,
    ```

5. Publish views, migrations etc.
  ```sh
  $ php artisan vendor:publish
  ```
  Or
  ```sh
  $ php artisan vendor:publish --provider="Larrock\ComponentCategory\LarrockComponentCategoryServiceProvider"
  ```
       
6. Run artisan command:
  ```sh
  $ php artisan larrock:check
  ```
  And follow the tips for setting third-party dependencies
  
  
7. Run migrations
  ```sh
  $ php artisan migrate
  ```

##START
http://yousite/admin/category