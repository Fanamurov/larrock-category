{{-- Список подразделов --}}
@if(count($data) === 0)
    <tr>
        <div class="uk-alert uk-alert-warning">Подразделов еще нет</div>
    </tr>
@endif
@foreach($data as $data_value)
    <tr>
        <td width="55">
            <a href="/admin/{{ $app->name }}/{{ $data_value->id }}">
                @if($data_value->getFirstMediaUrl('images', '110x110'))
                    <img style="width: 55px" src="{{ $data_value->getFirstMediaUrl('images', '110x110') }}">
                @else
                    <i class="icon-padding icon-color uk-icon-picture-o" title="Фото не прикреплено"></i>
                @endif
            </a>
        </td>
        <td>
            <a class="uk-h4" href="/admin/{{ $app->name }}/{{ $data_value->id }}">{{ $data_value->title }}</a>
            <br/>
            <a class="link-to-front" target="_blank" href="{{ $data_value->full_url }}">
                {{ str_limit($data_value->full_url, 35, '...') }}
            </a>
        </td>
        <td class="row-active">
            <div class="uk-button-group btn-group_switch_ajax" role="group" style="width: 100%">
                <button type="button" class="uk-button uk-button-primary uk-button-small @if($data_value->active === 0) uk-button-outline @endif"
                        data-row_where="id" data-value_where="{{ $data_value->id }}" data-table="category"
                        data-row="active" data-value="1" style="width: 50%"
                        data-toggle="tooltip" data-placement="bottom" title="Включить">on</button>
                <button type="button" class="uk-button uk-button-danger uk-button-small @if($data_value->active === 1) uk-button-outline @endif"
                        data-row_where="id" data-value_where="{{ $data_value->id }}" data-table="category"
                        data-row="active" data-value="0" style="width: 50%"
                        data-toggle="tooltip" data-placement="bottom" title="Выключить">off</button>
            </div>
        </td>
        <td class="row-position uk-hidden-small">
            <input type="text" name="position" value="{{ $data_value->position }}" class="ajax_edit_row uk-form-controls"
                   data-row_where="id" data-value_where="{{ $data_value->id }}" data-table="category"
                   data-toggle="tooltip" data-placement="bottom" title="Вес. Чем больше, тем выше в списках">
            <i class="uk-sortable-handle uk-icon uk-icon-bars uk-margin-small-right" title="Перенести материал по весу"></i>
        </td>
        <td class="row-edit uk-hidden-small">
            <a href="/admin/category/{{ $data_value->id }}/edit" class="uk-button uk-button-small">Свойства</a>
        </td>
        <td class="row-delete uk-hidden-small">
            <form action="/admin/category/{{ $data_value->id }}" method="post">
                <input name="_method" type="hidden" value="DELETE">
                {!! csrf_field() !!}
                <button type="submit" class="uk-button uk-button-small uk-button-danger please_conform">Удалить</button>
            </form>
        </td>
    </tr>
@endforeach