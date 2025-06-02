<?php

use yii\grid\GridView;
use yii\data\ActiveDataProvider;
use app\models\Stocks;
use yii\helpers\Html;
use yii\helpers\Json;

/** @var app\modules\api\modules\shoptet\controllers\StoresController $controller */

$this->title = 'Prodejny Kitos';
$this->params['breadcrumbs'][] = $this->title;

$dataProvider = new ActiveDataProvider([
    'query' => Stocks::filteredQuery()->orderBy('title'),
    'pagination' => [
        'pageSize' => 20,
    ],
]);

?>

<div class="stores-index">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::button('Přidat prodejnu', [
            'class' => 'btn btn-success',
            'id' => 'btn-add-new'
        ]) ?>
    </p>

    <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                'id',
                'title',
                'region',
                'delivery_point_address',    
                'shop_url',
                'gps_latitude',
                'gps_longitude',
                [
                  'label' => 'Otevírací doba',
                  'value' => function ($model) {
                      return $model->getFormattedOpeningHours();
                  },
                ],                                     
                'description',
                [
                    'label' => 'Telefon',
                    'value' => function ($model) {
                        return $model->phone ?: (preg_match('/tel\.\:\s*([0-9\s]+)/u', $model->delivery_point_address, $m) ? $m[1] : null);
                    },
                ],
                [
                    'label' => 'Email',
                    'value' => function ($model) {
                        return $model->email ?: (preg_match('/e-mail\:\s*([\w\.\-]+@[\w\-]+\.[\w\.]+)/ui', $model->delivery_point_address, $m) ? $m[1] : null);
                    },
                ],
                'image_url',
                'note',
                [
                    'label' => 'Mimořádná informace',
                    'format' => 'raw',
                    'value' => function ($model) {
                        return $model->extra_note
                            ? '<span style="color: red; font-weight: bold;">' . Html::encode($model->extra_note) . '</span>'
                            : null;
                    },
                ],
                [
                    'attribute' => 'visible',
                    'label' => 'Stav',
                    'format' => 'raw',
                    'value' => function ($model) {
                        return $model->visible
                            ? '<span class="badge badge-success">Zapnuto</span>'
                            : '<span class="badge badge-secondary">Vypnuto</span>';
                    }
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{update}',
                    'buttons' => [
                        'update' => function ($url, $model) {
                            return Html::a('Upravit', '#', [
                                'class' => 'btn btn-sm btn-primary btn-edit',
                                'data-id' => $model->id,
                                'data-model' => Json::htmlEncode([
                                    'id' => $model->id,
                                    'title' => $model->title,
                                    'region' => $model->region,
                                    'delivery_point_address' => $model->delivery_point_address,
                                    'shop_url' => $model->shop_url,
                                    'gps_latitude' => $model->gps_latitude,
                                    'gps_longitude' => $model->gps_longitude,
                                    'opening_hours' => $model->opening_hours ?: $model->parsedOpeningHours,
                                    'description' => $model->description,
                                    'phone' => $model->phone,
                                    'email' => $model->email,
                                    'image_url' => $model->image_url,
                                    'note' => $model->note,
                                    'extra_note' => $model->extra_note,
                                    'visible' => $model->visible,
                                ]),
                            ]);
                        },
                    ],
                ],
            ],
        ]) 
    ?>
</div>

<!-- Modal -->
<div id="editModal" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <form id="editForm">
        <div class="modal-header">
          <h5 class="modal-title">Upravit prodejnu</h5>
          <button type="button" class="close" id = "myCloseBtn" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="store-id">
          <div class="form-group">
            <label>Název</label>
            <input type="text" class="form-control" id="store-title">
          </div>
          <div class="form-group">
            <label>Region</label>
            <input type="text" class="form-control" id="store-region">
          </div>
          <div class="form-group">
            <label>Adresa</label>
            <input type="text" class="form-control" id="store-address">
          </div>
          <div class="form-group">
              <label>URL prodejny</label>
              <input type="text" class="form-control" id="store-url">
          </div>
          <div class="form-group">
            <label>GPS Latitude</label>
            <input type="text" class="form-control" id="store-lat">
          </div>
          <div class="form-group">
            <label>GPS Longitude</label>
            <input type="text" class="form-control" id="store-lng">
          </div>
          <!-- <div class="form-group">
            <label>Otevírací doba</label>
            <input type="text" class="form-control" id="store-hours">
          </div> -->
          <div class="form-group">
              <label>Otevírací doba</label>
              <table class="table table-bordered">
                <thead>
                  <tr>
                    <th>Den</th>
                    <th>Od</th>
                    <th>Do</th>
                    <th>Zavřeno</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>Pondělí</td>
                    <td><input type="time" class="form-control" id="opening-from-0"></td>
                    <td><input type="time" class="form-control" id="opening-to-0"></td>
                    <td><input type="checkbox" id="opening-closed-0"></td>
                  </tr>
                  <tr>
                    <td>Úterý</td>
                    <td><input type="time" class="form-control" id="opening-from-1"></td>
                    <td><input type="time" class="form-control" id="opening-to-1"></td>
                    <td><input type="checkbox" id="opening-closed-1"></td>
                  </tr>
                  <tr>
                    <td>Středa</td>
                    <td><input type="time" class="form-control" id="opening-from-2"></td>
                    <td><input type="time" class="form-control" id="opening-to-2"></td>
                    <td><input type="checkbox" id="opening-closed-2"></td>
                  </tr>
                  <tr>
                    <td>Čtvrtek</td>
                    <td><input type="time" class="form-control" id="opening-from-3"></td>
                    <td><input type="time" class="form-control" id="opening-to-3"></td>
                    <td><input type="checkbox" id="opening-closed-3"></td>
                  </tr>
                  <tr>
                    <td>Pátek</td>
                    <td><input type="time" class="form-control" id="opening-from-4"></td>
                    <td><input type="time" class="form-control" id="opening-to-4"></td>
                    <td><input type="checkbox" id="opening-closed-4"></td>
                  </tr>
                  <tr>
                    <td>Sobota</td>
                    <td><input type="time" class="form-control" id="opening-from-5"></td>
                    <td><input type="time" class="form-control" id="opening-to-5"></td>
                    <td><input type="checkbox" id="opening-closed-5"></td>
                  </tr>
                  <tr>
                    <td>Neděle</td>
                    <td><input type="time" class="form-control" id="opening-from-6"></td>
                    <td><input type="time" class="form-control" id="opening-to-6"></td>
                    <td><input type="checkbox" id="opening-closed-6"></td>
                  </tr>
              </tbody>
              </table>
              <input type="hidden" id="store-opening-hours">
          </div>
          <div class="form-group">
            <label>Popis</label>
            <textarea class="form-control" id="store-description"></textarea>
          </div>
          <div class="form-group">
            <label>Telefon</label>
            <input type="text" class="form-control" id="store-phone">
          </div>
          <div class="form-group">
            <label>Email</label>
            <input type="email" class="form-control" id="store-email">
          </div>
          <div class="form-group">
          <div class="form-group">
              <label>Fotografie prodejny</label>
              <input type="text" class="form-control" id="store-image">
              <img id="image-preview" src="" alt="Náhled" class="img-fluid mt-2 d-none" style="max-height: 200px;">
          </div>
          <div class="form-group">
            <label>Poznámka</label>
            <textarea class="form-control" id="store-note"></textarea>
          </div>
          <div class="form-group col-md-6">
            <label>
                <input type="checkbox" id="store-visible">
                Prodejna je aktivní (zobrazit)
            </label>
          </div>
          <div class="form-group">
          <label>Mimořádná informace</label>
          <textarea class="form-control" id="store-extra-note"></textarea>
        </div>  
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success" id="ulozit">Uložit</button>
          <button type="button" class="btn btn-secondary myCloseBtn"  data-dismiss="modal">Zavřít</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal -->
<div id="editModal" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <form id="editForm">
        <div class="modal-header">
          <h5 class="modal-title">Upravit prodejnu</h5>
          <button type="button" class="close" id = 'myCloseBtn' data-dismiss="modal" aria-label="Zavřít">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body row">
          <input type="hidden" id="store-id">
          <div class="form-group col-md-6">
            <label>Název</label>
            <input type="text" class="form-control" id="store-title">
          </div>
          <div class="form-group col-md-6">
            <label>Region</label>
            <input type="text" class="form-control" id="store-region">
          </div>
          <div class="form-group col-md-6">
            <label>Adresa</label>
            <input type="text" class="form-control" id="store-address">
          </div>
          <div class="form-group col-md-6">
            <label>GPS Latitude</label>
            <input type="text" class="form-control" id="store-lat">
          </div>
          <div class="form-group col-md-6">
            <label>GPS Longitude</label>
            <input type="text" class="form-control" id="store-lng">
          </div>
          <div class="form-group col-md-6">
            <label>Otevírací doba</label>
            <input type="text" class="form-control" id="store-hours">
          </div>
          <div class="form-group col-md-6">
            <label>Popis</label>
            <textarea class="form-control" id="store-description"></textarea>
          </div>
          <div class="form-group col-md-6">
            <label>Telefon</label>
            <input type="text" class="form-control" id="store-phone">
          </div>
          <div class="form-group col-md-6">
            <label>Email</label>
            <input type="email" class="form-control" id="store-email">
          </div>
          <div class="form-group">
            <label>Fotografie prodejny</label>
            <div class="custom-file">
                <input type="file" class="custom-file-input" id="upload-image">
                <label class="custom-file-label" for="upload-image">Vyber soubor...</label>
            </div>
            <input type="hidden" id="store-image">
            <img id="image-preview" src="" alt="Náhled" class="img-fluid mt-2 d-none" style="max-height: 200px;">
          </div>
          <div class="form-group col-md-6">
            <label>Poznámka</label>
            <textarea class="form-control" id="store-note"></textarea>
          </div>
          <div class="form-group col-md-6">
            <label>Mimořádná informace</label>
            <textarea class="form-control" id="store-extra-note"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">💾 Uložit</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Zavřít</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php
$this->registerCss(<<<CSS
  .modal-header{
    place-content: space-between;
  }
CSS);
$this->registerJs(<<<JS
function openEditModal(data = {}) {
    $('#store-id').val(data.id || '');
    $('#store-title').val(data.title || '');
    $('#store-region').val(data.region || '');
    $('#store-address').val(data.delivery_point_address || '');
    $('#store-url').val(data.shop_url || '');
    $('#store-lat').val(data.gps_latitude || '');
    $('#store-lng').val(data.gps_longitude || '');
    // $('#store-hours').val(data.opening_hours || '');
    let opening = {};
    try { opening = JSON.parse(data.opening_hours || '{}'); } catch(e) {}
    for (let i = 0; i < 7; i++) {
        $('#opening-from-'+i).val(opening[i]?.from && opening[i].from !== "Zavřeno" ? opening[i].from : '');
        $('#opening-to-'+i).val(opening[i]?.to && opening[i].to !== "Zavřeno" ? opening[i].to : '');
        $('#opening-closed-'+i).prop('checked', opening[i]?.closed ? true : false);
    }
    $('#store-description').val(data.description || '');
    $('#store-phone').val(data.phone || '');
    $('#store-email').val(data.email || '');
    $('#store-image').val(data.image_url || '');
    let imgUrl = data.image_url ? 'https://www.kitos.cz' + data.image_url : '';
    if (data.image_url) {
        $('#image-preview').attr('src', imgUrl).removeClass('d-none');
    } else {
        $('#image-preview').attr('src', '').addClass('d-none');
    }
    $('#store-note').val(data.note || '');
    $('#store-extra-note').val(data.extra_note || '');
    $('#store-visible').prop('checked',
        data.visible == 1 ||
        data.visible == '1' ||
        data.visible === true ||
        data.visible === 'true'
    );
    $('#editModal .modal-title').text(data.id ? 'Upravit prodejnu' : 'Nová prodejna');
    $('#editModal').modal('show');
}

$(document).on('click', '.myCloseBtn', function() {
    $('#editModal').modal('hide');
});

$(document).on('click', '#myCloseBtn', function() {
    $('#editModal').modal('hide');
});


$('#btn-add-new').on('click', function () {
    openEditModal();
});

$(document).on('click', '.btn-edit', function () {
    let data = $(this).data('model');
    openEditModal(data);
});

function getOpeningHoursJson() {
    let opening = {};
    for (let i = 0; i < 7; i++) {
        opening[i] = {
            from: $('#opening-from-'+i).val() || "Zavřeno",
            to: $('#opening-to-'+i).val() || "Zavřeno",
            closed: $('#opening-closed-'+i).is(':checked')
        };
        if (opening[i].closed) {
            opening[i].from = "Zavřeno";
            opening[i].to = "Zavřeno";
        }
    }
    return JSON.stringify(opening);
}

$('#store-image').on('input', function() {
    let relPath = $(this).val();
    let imgUrl = relPath ? 'https://www.kitos.cz' + relPath : '';
    if (relPath) {
        $('#image-preview').attr('src', imgUrl).removeClass('d-none');
    } else {
        $('#image-preview').attr('src', '').addClass('d-none');
    }
});

$('#editForm').on('submit', function(e) {
    e.preventDefault();
    let id = $('#store-id').val();
    let method = id ? 'PUT' : 'POST';
    let url = '/api/shoptet/stores' + (id ? '/' + id : '');
    $('#store-opening-hours').val(getOpeningHoursJson());

    let payload = {
        eshop: 'kitoscz',
        title: $('#store-title').val(),
        region: $('#store-region').val(),
        delivery_point_address: $('#store-address').val(),
        shop_url: $('#store-url').val(),
        gps_latitude: $('#store-lat').val(),
        gps_longitude: $('#store-lng').val(),
        opening_hours: $('#store-opening-hours').val(),
        description: $('#store-description').val(),
        phone: $('#store-phone').val(),
        email: $('#store-email').val(),
        image_url: $('#store-image').val(),
        note: $('#store-note').val(),
        extra_note: $('#store-extra-note').val(),
        visible: $('#store-visible').is(':checked') ? 1 : 0,
    };

    if (!id) {
        payload.main = 'no';
        payload.reservations = 'no';
        payload.is_delivery_point = 'no';
        payload.visible = 0;
        payload.shoptet_identifier = 'novy-' + Math.random().toString(36).substring(2, 8);
    }

    $.ajax({
        url: url,
        method: method,
        contentType: 'application/json',
        data: JSON.stringify(payload),
        success: function (response) {
            alert('Prodejna byla uložena (ID: ' + (response.id || id) + ')');
            location.reload();
        },
        error: function (xhr) {
            alert('Chyba při ukládání: ' + xhr.responseText);
        }
    });
    console.log('Form submitting...');
});


$(document).on('change', '#upload-image', function () {
    let id = $('#store-id').val();

    if (!id) {
        alert('Nejprve uložte prodejnu, poté můžete nahrát obrázek.');
        $(this).val('');
        return;
    }

    let fileInput = this;
    let file = fileInput.files[0];

    if (!file) return;

    let formData = new FormData();
    formData.append('image', file);

    $.ajax({
        url: '/api/shoptet/stores/' + id + '/upload-image',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function (resp) {
            if (resp.status === 'ok') {
                $('#store-image').val(resp.url);
                $('#image-preview').attr('src', resp.url).removeClass('d-none');
            } else {
                alert('Chyba při nahrávání: ' + (resp.error || 'Neznámá chyba.'));
            }
        },
        error: function (xhr) {
            alert('Chyba: ' + xhr.responseText);
        }
    });
});
JS);
?>

