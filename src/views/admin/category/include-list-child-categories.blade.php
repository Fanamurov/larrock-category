{{-- Список подразделов --}}
@if(count($data->get_child) === 0)
    <tr>
        <div class="uk-alert uk-alert-warning">Подразделов еще нет</div>
    </tr>
@endif
@foreach($data->get_child as $data_value)
    <tr>
        <td>
            <a href="/admin/{{ $app['name'] }}/{{ $data_value->id }}">
                @if($data_value->getFirstImage()->first())
                    <img style="width: 55px" src="{{ $data_value->getFirstImage()->first()->getUrl('110x110') }}">
                @else
                    <i class="icon-padding icon-color glyphicon glyphicon-picture" title="Фото не прикреплено"></i>
                @endif
            </a>
        </td>
        <td>
            <a class="h4" href="/admin/{{ $app['name'] }}/{{ $data_value->id }}">{{ $data_value->title }}</a>
            <br/>
            <a class="link-to-front" target="_blank" href="{{ $data_value->full_url }}">
                {{ str_limit($data_value->full_url, 35, '...') }}
            </a>
            @if($data_value->attached === 1)
                <span class="label label-warning">Прикреплен на главную</span>
            @endif
        </td>
        <td class="row-active">
            <div class="btn-group pull-right btn-group_switch_ajax" role="group">
                <button type="button" class="btn btn-xs btn-info @if($data_value->active === 0) btn-outline @endif"
                        data-row_where="id" data-value_where="{{ $data_value->id }}" data-table="category"
                        data-row="active" data-value="1"
                        data-toggle="tooltip" data-placement="bottom" title="Включить">on</button>
                <button type="button" class="btn btn-xs btn-danger @if($data_value->active === 1) btn-outline @endif"
                        data-row_where="id" data-value_where="{{ $data_value->id }}" data-table="category"
                        data-row="active" data-value="0"
                        data-toggle="tooltip" data-placement="bottom" title="Выключить">off</button>
            </div>
        </td>
        <td class="row-position">
            <input type="text" name="position" value="{{ $data_value->position }}" class="ajax_edit_row form-control"
                   data-row_where="id" data-value_where="{{ $data_value->id }}" data-table="category"
                   data-toggle="tooltip" data-placement="bottom" title="Вес. Чем больше, тем выше в списках">
            <i class="uk-sortable-handle uk-icon uk-icon-bars uk-margin-small-right" title="Перенести материал по весу"></i>
        </td>
        <td class="row-edit hidden-xs">
            <a href="/admin/category/{{ $data_value->id }}/edit" class="btn btn-info btn-xs">Свойства</a>
        </td>
        <td class="row-delete hidden-xs">
            <form action="/admin/category/{{ $data_value->id }}" method="post">
                <input name="_method" type="hidden" value="DELETE">
                <input type="hidden" name="_token" value="{{csrf_token()}}">
                <button type="submit" class="btn btn-danger btn-xs please_conform">Удалить</button>
            </form>
        </td>
    </tr>
@endforeach