{block name="head"}
{/block}


{block name="content"}
<div class="container">
  <div class="row">
    <div class="col col-md-6">
      <h1>Importar contas a pagar</h1>
      <form id="receber_form" method="post">
        <div class="form-group">
          <label for="">Arquivo .csv</label>
          <input id="receber_inputfile" class="form-control" type="file" name="" value="">
        </div>
        <div class="form-group">
          <button type="submit" name="button" class='btn btn-primary'>Importar</button>

          <div id="loading" class="spinner-grow text-primary" role="status">
            <span class="sr-only">Loading...</span>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
<script type="text/javascript">
  $(document).ready(function(){
    $('#loading').hide();

    $("#receber_form").on('submit', function(e){
      e.preventDefault();
      var formData = new FormData(e.target);
      var receber_inputfile = $("#receber_inputfile");
      var file = receber_inputfile[0].files[0];
      formData.append('file', file);
      $('#loading').show();

      $.ajax({
        url: '?ng=contaazul_csv/app/pagar-post',
        data: formData,
        processData: false,
        contentType: false,
        cache: false,
        dataType: 'json',
        method: 'POST',
      })
        .done(function(response){
          $('#loading').hide();

          if(response.status != "ok"){
            toastr.error(response.message);
            return ;
          }
          toastr.success(response.message);
        })
          .fail(function(){
            $('#loading').hide();

            toastr.error('Ocorreu um erro desconhecido');
          });

      return ;
    })
  });
</script>

{/block}
