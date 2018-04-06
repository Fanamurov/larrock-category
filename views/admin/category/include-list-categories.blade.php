{{-- Список подразделов --}}
@if(count($data) === 0)
    <tr>
        <td colspan="6">
            <div class="uk-alert uk-alert-warning">Подразделов еще нет</div>
        </td>
    </tr>
@endif
<tr class="tr-massiveAction">
    <td colspan="6">
        @include('larrock::admin.admin-builder.massive-action', ['data' => $data, 'app' => 'category', 'formId' => 'massiveActionCategory'])
    </td>
</tr>
@foreach($data as $data_value)
    <tr>
        <td width="55">
            <div class="actionSelect" data-target="massiveActionCategory" data-id="{{ $data_value->id }}">
                @if($data_value->getFirstMediaUrl('images', '110x110'))
                    <img style="width: 55px" src="{{ $data_value->getFirstMediaUrl('images', '110x110') }}">
                @else
                    <i uk-icon="icon: image; ratio: 2" title="Фото не прикреплено"></i>
                @endif
            </div>
        </td>
        <td>
            <a class="uk-h4" href="/admin/{{ $package->name }}/{{ $data_value->id }}">{{ $data_value->title }}</a>
            <br/>
            <a class="link-to-front" target="_blank" href="{{ $data_value->full_url }}">
                {{ str_limit($data_value->full_url, 35, '...') }}
            </a>
        </td>
        <td class="row-position uk-visible@s">
            <input type="text" name="position" value="{{ $data_value->position }}" class="ajax_edit_row uk-form-controls uk-input uk-form-small"
                   data-row_where="id" data-value_where="{{ $data_value->id }}" data-table="category"
                   data-toggle="tooltip" data-placement="bottom" title="Вес. Чем больше, тем выше в списках">
            <i class="uk-sortable-handle uk-icon uk-icon-bars uk-margin-small-right" title="Перенести материал по весу"></i>
        </td>
        <td class="row-active uk-visible@s">
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
        <td class="row-edit uk-visible@s">
            <a href="/admin/category/{{ $data_value->id }}/edit" class="uk-button uk-button-default uk-button-small">Свойства</a>
        </td>
        <td class="row-delete uk-visible@s">
            <form action="/admin/category/{{ $data_value->id }}" method="post">
                <input name="_method" type="hidden" value="DELETE">
                {!! csrf_field() !!}
                <button type="submit" class="uk-button uk-button-small uk-button-danger please_conform">Удалить</button>
            </form>
        </td>
    </tr>
@endforeach