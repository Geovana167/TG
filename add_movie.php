<?php 
  if(isset($_GET['movie_id'])){ 
    $page_title= 'Edit Movies';
  }
  else{ 
    $page_title='Add Movies'; 
  }
  $active_page="movies";

  $protocol = strtolower( substr( $_SERVER[ 'SERVER_PROTOCOL' ], 0, 5 ) ) == 'https' ? 'https' : 'http'; 

  $file_path = $protocol.'://'.$_SERVER['SERVER_NAME'] . dirname($_SERVER['REQUEST_URI']).'/';

  include("includes/header.php");
	require("includes/function.php");
	require("language/language.php");

	require_once("thumbnail_images.class.php");

	$cat_qry="SELECT * FROM tbl_language ORDER BY language_name";
  $cat_result=mysqli_query($mysqli,$cat_qry);

  $genre_qry="SELECT * FROM tbl_genres ORDER BY genre_name";
  $genre_result=mysqli_query($mysqli,$genre_qry); 
	
	//Get all Category 
	$qry="SELECT * FROM tbl_category";
	$result=mysqli_query($mysqli,$qry);
	
	 	
	if(isset($_POST['submit']) and isset($_GET['add']))
	{	
      $title=addslashes(trim($_POST['movie_title']));
      $desc=addslashes(trim($_POST['movie_desc']));

      $language_id=$_POST['language_id'];
      $genre_id=implode(',', $_POST['genre_id']);

      if($_POST['video_file_type']=='youtube_url'){

        $movie_url=$_POST['movie_url'];
        $youtube_video_url = addslashes($_POST['movie_url']);
        parse_str( parse_url( $youtube_video_url, PHP_URL_QUERY ), $array_of_vars );
        $video_id=  $array_of_vars['v'];
      }
      else if($_POST['video_file_type']=='server_url' OR $_POST['video_file_type']=='embedded_url'){
        $movie_url=$_POST['movie_url'];
        $video_id='';
      }else{
        $movie_url=$_POST['video_file_name'];
        $video_id='';
      }

      $protocol = strtolower( substr( $_SERVER[ 'SERVER_PROTOCOL' ], 0, 5 ) ) == 'https' ? 'https' : 'http';

      $file_path_dir = $protocol.'://'.$_SERVER['SERVER_NAME'] . dirname($_SERVER['REQUEST_URI']).'/images/';


      $folder_is="movies";

      if (!is_dir($file_path_dir.$folder_is)) {

          mkdir('images/movies', 0777, true);
      }

      $folder_is="thumbs";

      if (!is_dir($file_path_dir.'movies/'.$folder_is)) {

        mkdir('images/movies/thumbs', 0777, true);
      }


      // for movies poster
      $movie_poster=rand(0,99999)."_".$_FILES['movie_poster']['name'];
      $pic1=$_FILES['movie_poster']['tmp_name'];

            
      $tpath1='images/movies/'.$movie_poster; 
      copy($pic1,$tpath1);

      $thumbpath='images/movies/thumbs/'.$movie_poster;
        
      $obj_img = new thumbnail_images();
      $obj_img->PathImgOld = $tpath1;
      $obj_img->PathImgNew =$thumbpath;
      $obj_img->NewWidth = 270;
      $obj_img->NewHeight = 390;
      if (!$obj_img->create_thumbnail_images()) 
      {
        echo $_SESSION['msg']="Thumbnail not created... please upload image again";
        exit;
      }

      // for movies cover
      $movie_cover=rand(0,99999)."_".$_FILES['movie_cover']['name'];
      $pic1=$_FILES['movie_cover']['tmp_name'];

            
      $tpath1='images/movies/'.$movie_cover; 
      copy($pic1,$tpath1);

      $thumbpath='images/movies/thumbs/'.$movie_cover;
        
      $obj_img = new thumbnail_images();
      $obj_img->PathImgOld = $tpath1;
      $obj_img->PathImgNew =$thumbpath;
      $obj_img->NewWidth = 600;
      $obj_img->NewHeight = 350;
      if (!$obj_img->create_thumbnail_images()) 
      {
        echo $_SESSION['msg']="Thumbnail not created... please upload image again";
        exit;
      }
          
      $data = array( 
        'language_id'  =>  $language_id,
        'genre_id'  =>  $genre_id,
        'movie_type'  =>  $_POST['video_file_type'],
        'movie_title'  =>  $title,
        'movie_cover'  =>  $movie_cover,
        'movie_poster'  =>  $movie_poster,
        'movie_url'  =>  $movie_url,
        'video_id'  =>  $video_id,
        'movie_desc'  =>  $desc
        );    

      $qry = Insert('tbl_movies',$data);      

      $_SESSION['msg']="10";
   
      header( "Location:manage_movies.php");
      exit;
	}

  if(isset($_GET['movie_id']))
  {  
    $qry="SELECT * FROM tbl_movies where id='".$_GET['movie_id']."'";
    $result=mysqli_query($mysqli,$qry);
    $row=mysqli_fetch_assoc($result);
  }
  if(isset($_POST['submit']) and isset($_GET['movie_id']))
  { 
    $title=addslashes(trim($_POST['movie_title']));
    $desc=addslashes(trim($_POST['movie_desc']));

    $language_id=$_POST['language_id'];
    $genre_id=implode(',', $_POST['genre_id']);

    if($_POST['video_file_type']=='youtube_url'){

      $movie_url=$_POST['movie_url'];
      $youtube_video_url = addslashes($_POST['movie_url']);
      parse_str( parse_url( $youtube_video_url, PHP_URL_QUERY ), $array_of_vars );
      $video_id=  $array_of_vars['v'];
    }
    else if($_POST['video_file_type']=='server_url' OR $_POST['video_file_type']=='embedded_url'){
      $movie_url=$_POST['movie_url'];
      $video_id='';
    }else{
      $movie_url=$_POST['video_file_name'];
      $video_id='';
    }
    
    if($_FILES['movie_poster']['error']!=4){

      unlink('images/movies/'.$row['movie_poster']);
      unlink('images/movies/thumbs/'.$row['movie_poster']);

      // for movie poster
      $movie_poster=rand(0,99999)."_".$_FILES['movie_poster']['name'];
      $pic1=$_FILES['movie_poster']['tmp_name'];

            
      $tpath1='images/movies/'.$movie_poster; 
      copy($pic1,$tpath1);

      $thumbpath='images/movies/thumbs/'.$movie_poster;
        
      $obj_img = new thumbnail_images();
      $obj_img->PathImgOld = $tpath1;
      $obj_img->PathImgNew =$thumbpath;
      $obj_img->NewWidth = 270;
      $obj_img->NewHeight = 390;
      if (!$obj_img->create_thumbnail_images()) 
      {
        echo $_SESSION['msg']="Thumbnail not created... please upload image again";
        exit;
      }
    }else{
      $movie_poster=$row['movie_poster'];
    }

    if($_FILES['movie_cover']['error']!=4){
      unlink('images/movies/'.$row['movie_cover']);
      unlink('images/movies/thumbs/'.$row['movie_cover']);

      // for movies cover
      $movie_cover=rand(0,99999)."_".$_FILES['movie_cover']['name'];
      $pic1=$_FILES['movie_cover']['tmp_name'];

            
      $tpath1='images/movies/'.$movie_cover; 
      copy($pic1,$tpath1);

      $thumbpath='images/movies/thumbs/'.$movie_cover;
        
      $obj_img = new thumbnail_images();
      $obj_img->PathImgOld = $tpath1;
      $obj_img->PathImgNew =$thumbpath;
      $obj_img->NewWidth = 600;
      $obj_img->NewHeight = 350;
      if (!$obj_img->create_thumbnail_images()) 
      {
        echo $_SESSION['msg']="Thumbnail not created... please upload image again";
        exit;
      }

    }else{
      $movie_cover=$row['movie_cover'];
    }

    $data = array( 
        'language_id'  =>  $language_id,
        'genre_id'  =>  $genre_id,
        'movie_type'  =>  $_POST['video_file_type'],
        'movie_title'  =>  $title,
        'movie_poster'  =>  $movie_poster,
        'movie_cover'  =>  $movie_cover,
        'movie_url'  =>  $movie_url,
        'video_id'  =>  $video_id,
        'movie_desc'  =>  $desc
        );

    $edit=Update('tbl_movies', $data, "WHERE id = '".$_GET['movie_id']."'");

    $_SESSION['msg']="11"; 
    header( "Location:manage_movies.php");
    exit;
  }
	 
?>
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    
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
                    <label class="col-md-3 control-label">Language :-</label>
                    <div class="col-md-6">
                      <select name="language_id" id="language_id" class="select2" required>
                        <option value="">--Select Language--</option>
                        <?php
                            while($data=mysqli_fetch_array($cat_result))
                            {
                        ?>                       
                        <option value="<?php echo $data['id'];?>" <?php if(isset($_GET['movie_id']) && $row['language_id']==$data['id']){ echo 'selected'; } ?>><?php echo $data['language_name'];?></option>                          
                        <?php
                          }
                        ?>
                      </select>
                    </div>
                  </div> 
                   <div class="form-group">
                    <label class="col-md-3 control-label">Genre :-</label>
                    <div class="col-md-6">
                      <select name="genre_id[]" id="genre_id" class="select2" required multiple="">
                        <option value="">--Select Genre--</option>
                        <?php
                            while($genre_row=mysqli_fetch_array($genre_result))
                            {
                        ?>                       
                        <option value="<?php echo $genre_row['gid'];?>" <?php $genre_list=explode(",", $row['genre_id']);
                                foreach($genre_list as $ids)
                                {if($genre_row['gid']==$ids){ echo 'selected'; }}?>><?php echo $genre_row['genre_name'];?></option>                           
                        <?php
                          }
                        ?>
                      </select>
                    </div>
                  </div>                 
                  <div class="form-group">
                    <label class="col-md-3 control-label">Movie Title :-</label>
                    <div class="col-md-6">
                      <input type="text" name="movie_title" id="movie_title" value="<?php if(isset($_GET['movie_id'])){echo $row['movie_title'];}?>" class="form-control" required>
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-md-3 control-label">Video Upload Type :-</label>
                    <div class="col-md-6">                       
                      <select name="video_file_type" id="video_file_type" class="select2" required>
                            <option value="">--Select Type--</option>
                            <option value="youtube_url" <?php if(isset($_GET['movie_id']) && $row['movie_type']=='youtube_url'){ echo 'selected'; } ?>>YouTube URL</option>
                            <option value="server_url" <?php if(isset($_GET['movie_id']) && $row['movie_type']=='server_url'){ echo 'selected'; } ?>>Live URL</option>
                            <option value="local" <?php if(isset($_GET['movie_id']) && $row['movie_type']=='local'){ echo 'selected'; } ?>>Local System</option>
                            <option value="embedded_url" <?php if(isset($_GET['movie_id']) && $row['movie_type']=='embedded_url'){ echo 'selected'; } ?>>Embedded URL (Open Load, Very Stream, Daily motion, Vimeo)</option>
                            
                      </select>
                    </div>
                  </div>
                  <div id="movie_url_holder" class="form-group" style="display:none;">
                    <label class="col-md-3 control-label">Enter URL :-</label>
                    <div class="col-md-6">
                      <input type="text" name="movie_url" id="movie_url" value="<?php if(isset($_GET['movie_id'])){ echo $row['movie_url'];}?>" class="form-control">
                    </div>
                  </div>
                  <div id="movie_local_holder" class="form-group" style="display:none;">
                    <label class="col-md-3 control-label">Browse Video :-</label>
                    <div class="col-md-6">
                    
                      <input type="hidden" name="video_file_name" id="video_file_name" value="<?php if(isset($_GET['movie_id']) && $row['movie_type']=='local'){ echo $row['movie_url'];}?>">
                      <input type="file" name="video_local" id="video_local" value="" class="form-control">
                        <?php 
                          if(isset($_GET['movie_id'])){
                         ?> 
                          <div><label class="control-label">Current URL :-</label><?php echo $file_path.'uploads/'.$row['movie_url']?></div><br>
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
                      <p class="control-label-help" id="square_lable_info">(Recommended resolution: 185x278 portrait)</p>
                    </label>
                    <div class="col-md-6">
                      <div class="fileupload_block">
                        <input type="file" name="movie_poster" value="" id="fileupload">
                        <div class="fileupload_img">
                          <?php 
                            $img_src="";

                            if(!isset($_GET['movie_id']) || !file_exists('images/movies/'.$row['movie_poster'])){
                              $img_src='assets/images/series-poster.jpg';
                            }else{
                              $img_src='images/movies/'.$row['movie_poster'];
                            }

                          ?>
                          <img type="image" src="<?=$img_src?>" alt="poster image" style="width: 80px;height: 115px" />
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="form-group">
                    <label class="col-md-3 control-label">Cover Image:-
                      <p class="control-label-help" id="square_lable_info">(Recommended resolution: 500x282 landscape)</p>
                    </label>
                    <div class="col-md-6">
                      <div class="fileupload_block">
                        <input type="file" name="movie_cover" value="" id="fileupload">
                        <div class="fileupload_img">
                          <?php 
                            $img_src="";

                            if(!isset($_GET['movie_id']) || !file_exists('images/movies/'.$row['movie_cover'])){
                              $img_src='assets/images/series-cover.jpg';
                            }else{
                              $img_src='images/movies/'.$row['movie_cover'];
                            }

                          ?>
                          <img type="image" src="<?=$img_src?>" alt="cover image" style="width: 150px;height: 86px" />
                        </div>
                      </div>
                    </div>
                  </div>
                  
                  <div class="form-group">
                    <div class="col-md-3">
                      <label class="control-label">Movie Description :-</label>
                    </div>
                    <div class="col-md-6">
                      <textarea name="movie_desc" id="movie_desc" rows="5" class="form-control"><?php if(isset($_GET['movie_id'])){ echo $row['movie_desc']; } ?></textarea>
                      <script>
                        CKEDITOR.replace('movie_desc');
                      </script>
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
        $("input[name='movie_poster']").next(".fileupload_img").find("img").attr('src', e.target.result);
      }
      
      reader.readAsDataURL(input.files[0]);
    }
  }
  $("input[name='movie_poster']").change(function() { 
    readURL(this);
  });

  function readURL1(input) {
    if (input.files && input.files[0]) {
      var reader = new FileReader();
      
      reader.onload = function(e) {
        $("input[name='movie_cover']").next(".fileupload_img").find("img").attr('src', e.target.result);
      }
      
      reader.readAsDataURL(input.files[0]);
    }
  }
  $("input[name='movie_cover']").change(function() { 
    readURL1(this);
  });


  $("#video_file_type").on("change",function(e){
    var _type=$(this).val();

    if(_type=='youtube_url' || _type=='server_url' || _type=='embedded_url'){
      $("#movie_url_holder").show();
      $("#movie_local_holder").hide();
    }else if(_type=='local'){
      $("#movie_local_holder").show();
      $("#movie_url_holder").hide();
    }else{
      $("#movie_local_holder").hide();
      $("#movie_url_holder").hide();
    }
  });

  // for edit
  var _type=$("#video_file_type").val();

  if(_type=='youtube_url' || _type=='server_url' || _type=='embedded_url'){
    $("#movie_url_holder").show();
    $("#movie_local_holder").hide();
  }else if(_type=='local'){
    $("#movie_local_holder").show();
    $("#movie_url_holder").hide();
  }else{
    $("#movie_local_holder").hide();
    $("#movie_url_holder").hide();
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
