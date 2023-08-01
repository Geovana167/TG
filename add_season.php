<?php 
  $page_title='Add Season'; 
  $active_page="series";

  include("includes/header.php");
  require("includes/function.php");
  require("language/language.php");

  require_once("thumbnail_images.class.php");

  $sql="SELECT * FROM tbl_series ORDER BY series_name";
  $result_series=mysqli_query($mysqli,$sql);

  if(isset($_POST['submit']) and isset($_GET['add']))
  {
  
    $series_id=addslashes(trim($_POST['series_id']));

    foreach ($_POST['season_name'] as $key => $value) {
       $data = array(
            'series_id'  =>  $series_id,
            'season_name'  =>  $value
        );  

      $qry = Insert('tbl_season',$data); 
    }
     
    $_SESSION['msg']="10";
    header( "Location:manage_season.php");
    exit; 

  }

?>

<div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="page_title_block">
            <div class="col-md-5 col-xs-12">
              <div class="page_title"><?=$page_title?></div>
            </div>
          </div>
          <div class="clearfix"></div>
          <div class="row mrg-top">
            <div class="col-md-12">
               
              <div class="col-md-12 col-sm-12">
                <?php if(isset($_SESSION['msg'])){?> 
                 <div class="alert alert-success alert-dismissible" role="alert"> <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                  <?php echo $client_lang[$_SESSION['msg']] ; ?></a> </div>
                <?php unset($_SESSION['msg']);}?> 
              </div>
            </div>
          </div>
          <div class="card-body mrg_bottom"> 
            <form action="" name="addeditlanguage" method="post" class="form form-horizontal" enctype="multipart/form-data">

              <div class="section">
                <div class="section-body">
                  
                  <div class="form-group">
                    <label class="col-md-3 control-label">Series :-</label>
                    <div class="col-md-6">
                      <select name="series_id" id="series_id" class="select2" required>
                        <option value="">--Select Series--</option>
                        <?php
                            while($data=mysqli_fetch_array($result_series))
                            {
                        ?>                       
                        <option value="<?php echo $data['id'];?>"><?php echo $data['series_name'];?></option>                          
                        <?php
                          }
                        ?>
                      </select>
                    </div>
                  </div> 
                  <div class="input-container">
                    <div class="form-group" style="margin-bottom: 10px">
                      <label class="col-md-3 control-label">Season name :-</label>
                      <div class="col-md-6">
                        <input type="text" name="season_name[]" class="form-control" style="margin-bottom: 5px" required>
                        <a href="" class="btn_remove" style="float: right;color: red;">&times; Remove</a>
                      </div>
                    </div>
                  </div>
                  <div id="dynamicInput"></div>
                  <div class="form-group">
                    <div class="col-md-9 col-md-offset-3">                      
                      <button type="button" class="btn btn-success btn-xs add_more">Add More Season</button>
                    </div>
                  </div>

                  <br>
                  <div class="form-group">
                    <div class="col-md-9 col-md-offset-3">
                      <button type="submit" name="submit" class="btn btn-primary">Save</button>
                    </div>
                  </div>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>


        
<?php include("includes/footer.php");?>       

<script type="text/javascript">

  $(".btn_remove:eq(0)").hide();

  $(".add_more").click(function(e){

    var _html=$(".input-container").html();
      
    $("#dynamicInput").append(_html);

    $(".btn_remove:not(:eq(0))").show();

    $(".btn_remove").click(function(e){
      e.preventDefault();
      $(this).parents(".form-group").remove();
    });
  });

  $(".btn_remove").click(function(e){
    e.preventDefault();
    $(this).parents(".form-group").remove();
  });
</script>