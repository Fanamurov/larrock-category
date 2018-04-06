{{-- Создание раздела --}}
<tr class="create-category uk-hidden">
    <form action="/admin/category/storeEasy" method="post" class="uk-form">
        <td></td>
        <td>
            <div class="create-category-title_div uk-form">
                <input type="text" placeholder="Название раздела" class="uk-form-controls create-category-title uk-width-1-1 uk-input" name="title">
            </div>
        </td>
        <td class="row-position uk-form uk-hidden-small">
            <input type="text" name="position" value="0" class="uk-form-controls uk-input uk-form-width-small"
                   data-toggle="tooltip" data-placement="bottom" title="Вес. Чем больше, тем выше в списках">
        </td>
        <td colspan="3">
            {!! csrf_field() !!}
            @if($parent && $parent > 0)
                <input type="hidden" name="parent" value="{{ $parent }}">
            @endif
            <input type="hidden" name="url" value="novyy-material">
            <input type="hidden" name="component" value="{{ $component }}">
            <button class="uk-button uk-button-primary" name="save_category_easy">Создать</button>
        </td>
    </form>
</tr>