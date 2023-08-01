<?php 
  if(isset($_GET['episode_id'])){ 
    $page_title= 'Edit Episode';
  }
  else{ 
    $page_title='Add Episode'; 
  }
  $active_page="series";

  $protocol = strtolower( substr( $_SERVER[ 'SERVER_PROTOCOL' ], 0, 5 ) ) == 'https' ? 'https' : 'http'; 

  $file_path = $protocol.'://'.$_SERVER['SERVER_NAME'] . dirname($_SERVER['REQUEST_URI']).'/';

  include("includes/header.php");
	require("includes/function.php");
	require("language/language.php");

	require_once("thumbnail_images.class.php");

	$cat_qry="SELECT * FROM tbl_series ORDER BY series_name";
  $cat_result=mysqli_query($mysqli,$cat_qry);
	 	
	if(isset($_POST['submit']) and isset($_GET['add']))
	{	
      $title=addslashes(trim($_POST['episode_title']));

      $series_id=$_POST['series_id'];
      $season_id=$_POST['season_id'];


      if($_POST['video_file_type']=='youtube_url'){

        $episode_url=$_POST['episode_url'];
        $youtube_video_url = addslashes($_POST['episode_url']);
        parse_str( parse_url( $youtube_video_url, PHP_URL_QUERY ), $array_of_vars );
        $video_id=  $array_of_vars['v'];
      }
      else if($_POST['video_file_type']=='server_url' OR $_POST['video_file_type']=='embedded_url'){
        $episode_url=$_POST['episode_url'];
        $video_id='';
      }else{
        $episode_url=$_POST['video_file_name'];
        $video_id='';
      }

      $protocol = strtolower( substr( $_SERVER[ 'SERVER_PROTOCOL' ], 0, 5 ) ) == 'https' ? 'https' : 'http';

      $file_path_dir = $protocol.'://'.$_SERVER['SERVER_NAME'] . dirname($_SERVER['REQUEST_URI']).'/images/';


      $folder_is="episodes";

      if (!is_dir($file_path_dir.$folder_is)) {

        mkdir('images/episodes', 0777, true);
      }

      $folder_is="thumbs";

      if (!is_dir($file_path_dir.'episodes/'.$folder_is)) {

        mkdir('images/episodes/thumbs', 0777, true);
      }

      // for episode poster
      $ext = pathinfo($_FILES['episode_poster']['name'], PATHINFO_EXTENSION);
      $episode_poster=date('dmYhis').'_'.rand(0,99999).".".$ext;
      $pic1=$_FILES['episode_poster']['tmp_name'];

            
      $tpath1='images/episodes/'.$episode_poster; 
      copy($pic1,$tpath1);

      $thumbpath='images/episodes/thumbs/'.$episode_poster;
        
      $obj_img = new thumbnail_images();
      $obj_img->PathImgOld = $tpath1;
      $obj_img->PathImgNew =$thumbpath;
      $obj_img->NewWidth = 300;
      $obj_img->NewHeight = 300;
      if (!$obj_img->create_thumbnail_images()) 
      {
        echo $_SESSION['msg']="Thumbnail not created... please upload image again";
        exit;
      }
          
      $data = array( 
        'series_id'  =>  $series_id,
        'season_id'  =>  $season_id,
        'episode_title'  =>  $title,
        'episode_type'  =>  $_POST['video_file_type'],
        'episode_url'  =>  $episode_url,
        'video_id'  =>  $video_id,
        'episode_poster'  =>  $episode_poster
        );    

      $qry = Insert('tbl_episode',$data);      

      $_SESSION['msg']="10";
   
      header( "Location:manage_episode.php");
      exit;
	}

  if(isset($_GET['episode_id']))
  {  
    $qry="SELECT * FROM tbl_episode where id='".$_GET['episode_id']."'";
    $result=mysqli_query($mysqli,$qry);
    $row=mysqli_fetch_assoc($result);
  }
  if(isset($_POST['submit']) and isset($_GET['episode_id']))
  { 
    $title=addslashes(trim($_POST['episode_title']));

    $series_id=$_POST['series_id'];
    $season_id=$_POST['season_id'];


    if($_POST['video_file_type']=='youtube_url'){

      $episode_url=$_POST['episode_url'];
      $youtube_video_url = addslashes($_POST['episode_url']);
      parse_str( parse_url( $youtube_video_url, PHP_URL_QUERY ), $array_of_vars );
      $video_id=  $array_of_vars['v'];
    }
    else if($_POST['video_file_type']=='server_url' OR $_POST['video_file_type']=='embedded_url'){
      $episode_url=$_POST['episode_url'];
      $video_id='';
    }else{
      $episode_url=$_POST['video_file_name'];
      $video_id='';
    }

    
    if($_FILES['episode_poster']['error']!=4){

      unlink('images/episodes/'.$row['episode_poster']);
      unlink('images/episodes/thumbs/'.$row['episode_poster']);

      // for movie poster
      $ext = pathinfo($_FILES['episode_poster']['name'], PATHINFO_EXTENSION);
      $episode_poster=date('dmYhis').'_'.rand(0,99999).".".$ext;
      $pic1=$_FILES['episode_poster']['tmp_name'];

            
      $tpath1='images/episodes/'.$episode_poster; 
      copy($pic1,$tpath1);
      $thumbpath='images/episodes/thumbs/'.$episode_poster;
        
      $obj_img = new thumbnail_images();
      $obj_img->PathImgOld = $tpath1;
      $obj_img->PathImgNew =$thumbpath;
      $obj_img->NewWidth = 300;
      $obj_img->NewHeight = 300;
      if (!$obj_img->create_thumbnail_images()) 
      {
        echo $_SESSION['msg']="Thumbnail not created... please upload image again";
        exit;
      }
      }else{
        $episode_poster=$row['episode_poster'];
      }

      $data = array( 
        'series_id'  =>  $series_id,
        'season_id'  =>  $season_id,
        'episode_title'  =>  $title,
        'episode_type'  =>  $_POST['video_file_type'],
        'episode_url'  =>  $episode_url,
        'video_id'  =>  $video_id,
        'episode_poster'  =>  $episode_poster
        );

      $edit=Update('tbl_episode', $data, "WHERE id = '".$_GET['episode_id']."'");

      $_SESSION['msg']="11"; 
      header( "Location:manage_episode.php");
      exit;
  }
	 
?>
<style type="text/css">
  .msg{
    padding: 5px 0px;
    font-size: 14px;
    font-weight: 500;
  }
</style>
    
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="page_title_block">
            <div class="col-md-5 col-xs-12">
              <div class="page_title"><?=$page_title?></div>
            </div>
          </div>
          <div class="clearfix"></div>
          <div class="card-body mrg_bottom">
            <form class="form form-horizontal" action="" method="post"  enctype="multipart/form-data" onsubmit="return checkValidation(this);">
              <div class="section">
                <div class="section-body">
                  <div class="form-group">
                    <label class="col-md-3 control-label">Series :-</label>
                    <div class="col-md-6">
                      <select name="series_id" id="series_id" class="select2" required>
                        <option value="">--Select Series--</option>
                        <?php
                            while($data=mysqli_fetch_array($cat_result))
                            {
                        ?>                       
                        <option value="<?php echo $data['id'];?>" <?php if(isset($_GET['episode_id']) && $row['series_id']==$data['id']){ echo 'selected'; } ?>><?php echo $data['series_name'];?></option>                          
                        <?php
                          }
                        ?>
                      </select>
                    </div>
                  </div> 
                   <div class="form-group">
                    <label class="col-md-3 control-label">Season :-</label>
                    <div class="col-md-6">
                      <input type="hidden" class="old_season_id" value="<?php if(isset($_GET['episode_id'])){ echo $row['season_id'];}?>">
                      <select name="season_id" id="season_id" class="select2" required>
                        <option value="">--Select Season--</option>
                      </select>
                    </div>
                  </div>                 
                  <div class="form-group">
                    <label class="col-md-3 control-label">Episode Title :-</label>
                    <div class="col-md-6">
                      <input type="text" name="episode_title" id="episode_title" value="<?php if(isset($_GET['episode_id'])){ echo $row['episode_title'];}?>" class="form-control" required>
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-md-3 control-label">Video Upload Type :-</label>
                    <div class="col-md-6">                       
                      <select name="video_file_type" id="video_file_type" class="select2" required>
                            <option value="">--Select Type--</option>
                            <option value="youtube_url" <?php if(isset($_GET['episode_id']) && $row['episode_type']=='youtube_url'){ echo 'selected'; } ?>>YouTube URL</option>
                            <option value="server_url" <?php if(isset($_GET['episode_id']) && $row['episode_type']=='server_url'){ echo 'selected'; } ?>>Live URL</option>
                            <option value="local" <?php if(isset($_GET['episode_id']) && $row['episode_type']=='local'){ echo 'selected'; } ?>>Local System</option>
                            <option value="embedded_url" <?php if(isset($_GET['episode_id']) && $row['movie_type']=='embedded_url'){ echo 'selected'; } ?>>Embedded URL (Open Load, Very Stream, Daily motion, Vimeo)</option>
                      </select>
                    </div>
                  </div>
                  <div id="episode_url_holder" class="form-group" style="display:none;">
                    <label class="col-md-3 control-label">Enter URL :-</label>
                    <div class="col-md-6">
                      <input type="text" name="episode_url" id="episode_url" value="<?php if(isset($_GET['episode_id'])){ echo $row['episode_url'];}?>" class="form-control">
                    </div>
                  </div>
                  <div id="episode_local_holder" class="form-group" style="display:none;">
                    <label class="col-md-3 control-label">Browse Video :-</label>
                    <div class="col-md-6">
                    
                      <input type="hidden" name="video_file_name" id="video_file_name" value="<?php if(isset($_GET['episode_id']) && $row['episode_type']=='local'){ echo $row['episode_url'];}?>">
                      <input type="file" name="video_local" id="video_local" value="" class="form-control">
                        <?php 
                          if(isset($_GET['episode_id'])){
                         ?> 
                          <div><label class="control-label">Current URL :-</label><?php echo $file_path.'uploads/'.$row['episode_url']?></div><br>
                          <?php  
                        }
                        ?>
                        <div class="progress" style="margin-bottom: 5px">
                            <div class="progress-bar progress-bar-success myprogress" role="progressbar" style="width:0%">0%</div>
                        </div>

                        <div class="msg"></div>
                        <button type="button" id="btn_upload" class="btn btn btn-success"><i class="fa fa-upload"></i> Upload Now</button>
                    </div>
                  </div><br>
                  <div class="form-group">
                    <label class="col-md-3 control-label">Poster Image:-
                      <p class="control-label-help" id="square_lable_info">(Recommended resolution: landscape, 500x282)</p>
                    </label>
                    <div class="col-md-6">
                      <div class="fileupload_block">
                        <input type="file" name="episode_poster" value="" id="fileupload">
                        <div class="fileupload_img">
                          <?php 
                            $img_src="";

                            if(!isset($_GET['episode_id']) || !file_exists('images/episodes/'.$row['episode_poster'])){
                              $img_src='assets/images/series-cover.jpg';
                            }else{
                              $img_src='images/episodes/'.$row['episode_poster'];
                            }

                          ?>
                          <img type="image" src="<?=$img_src?>" alt="poster image" style="width: 150px;height: 86px" />
                        </div>
                      </div>
                    </div>
                  </div>
                  
                  <div class="form-group">&nbsp;</div>
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

  function readURL(input) {
    if (input.files && input.files[0]) {
      var reader = new FileReader();
      
      reader.onload = function(e) {
        $("input[name='episode_poster']").next(".fileupload_img").find("img").attr('src', e.target.result);
      }
      
      reader.readAsDataURL(input.files[0]);
    }
  }
  $("input[name='episode_poster']").change(function() { 
    readURL(this);
  });


  $("#series_id").on("change",function(e){
    $("#season_id").html('<option value="">--Select Season--</option>');
    var _id=$(this).val();
    $.ajax({
      type:'post',
      url:'processData.php',
      dataType:'json',
      data:{id:_id,'action':'getSeason'},
      success:function(res){
          console.log(res);
          if(res.status=='1'){
            $.each(res.data, function(index, item) {
                $("#season_id").append(item);
            });
          }
        }
    });
  });

  
  var _id=$("#series_id").val();
  if(_id!=''){
    var _old_id=$(".old_season_id").val();
    $("#season_id").html('<option value="">--Select Season--</option>');
    $.ajax({
      type:'post',
      url:'processData.php',
      dataType:'json',
      data:{id:_id,'action':'getSeason'},
      success:function(res){
          console.log(res);
          if(res.status=='1'){
            $.each(res.data, function(index, item) {
                $("#season_id").append(item);

            });
            $('#season_id option[value="'+_old_id+'"]').prop('selected', true);
          }
        }
    });
  }
  



  // to hide and show based on video upload type

  $("#video_file_type").on("change",function(e){
    var _type=$(this).val();

    if(_type=='youtube_url' || _type=='server_url' || _type=='embedded_url'){
      $("#episode_url_holder").show();
      $("#episode_local_holder").hide();
    }else if(_type=='local'){
      $("#episode_local_holder").show();
      $("#episode_url_holder").hide();
    }else{
      $("#episode_local_holder").hide();
      $("#episode_url_holder").hide();
    }
  });

  // for edit
  var _type=$("#video_file_type").val();

  if(_type=='youtube_url' || _type=='server_url' || _type=='embedded_url'){
    $("#episode_url_holder").show();
    $("#episode_local_holder").hide();
  }else if(_type=='local'){
    $("#episode_local_holder").show();
    $("#episode_url_holder").hide();
  }else{
    $("#episode_local_holder").hide();
    $("#episode_url_holder").hide();
  }


  // to upload local video

  $('#btn_upload').click(function () {
    $('.myprogress').css('width', '0');
    $('.msg').text('');
    var video_local = $('#video_local').val();
    if (video_local == '') {
        alert('Please enter file name and select file');
        return;
    }
    var formData = new FormData();
    formData.append('video_local', $('#video_local')[0].files[0]);
    $('#btn_upload').attr('disabled', 'disabled');
     $('.msg').text('Uploading in progress...').css("color","organge");
    $.ajax({
        url: 'uploadscript.php',
        data: formData,
        processData: false,
        contentType: false,
        type: 'POST',
        // this part is progress bar
        xhr: function () {
            var xhr = new window.XMLHttpRequest();
            xhr.upload.addEventListener("progress", function (evt) {
                if (evt.lengthComputable) {
                    var percentComplete = evt.loaded / evt.total;
                    percentComplete = parseInt(percentComplete * 100);
                    $('.myprogress').text(percentComplete + '%');
                    $('.myprogress').css('width', percentComplete + '%');
                }
            }, false);
            return xhr;
        },
        success: function (data) {
         
            $('#video_file_name').val(data);
            $('.msg').html('<i class="fa fa-check-circle-o"></i> File uploaded successfully').css("color","green");
            $('#btn_upload').removeAttr('disabled');
        }
    });
});


</script>    
