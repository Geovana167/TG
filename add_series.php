<?php 
	if(isset($_GET['series_id'])){ 
		$page_title= 'Edit Series';
	}
	else{ 
		$page_title='Add Series'; 
	}

	$active_page="series";
	include("includes/header.php");
	require("includes/function.php");
	require("language/language.php");

	require_once("thumbnail_images.class.php");
	
	if(isset($_POST['submit']) and isset($_GET['add']))
	{
		
		$title=addslashes(trim($_POST['series_name']));
		$desc=addslashes(trim($_POST['series_desc']));

		// for series poster
		$series_poster=rand(0,99999)."_".$_FILES['series_poster']['name'];
		$pic1=$_FILES['series_poster']['tmp_name'];

		$protocol = strtolower( substr( $_SERVER[ 'SERVER_PROTOCOL' ], 0, 5 ) ) == 'https' ? 'https' : 'http';

		$file_path_dir = $protocol.'://'.$_SERVER['SERVER_NAME'] . dirname($_SERVER['REQUEST_URI']).'/images/';


		$folder_is="series";

		if (!is_dir($file_path_dir.$folder_is)) {

		  mkdir('images/series', 0777, true);
		}

		$folder_is="thumbs";

		if (!is_dir($file_path_dir.'series/'.$folder_is)) {

			mkdir('images/series/thumbs', 0777, true);
		}

					
		$tpath1='images/series/'.$series_poster; 
		copy($pic1,$tpath1);

		$thumbpath='images/series/thumbs/'.$series_poster;
			
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

		// for series cover
		$series_cover=rand(0,99999)."_".$_FILES['series_cover']['name'];
		$pic1=$_FILES['series_cover']['tmp_name'];

					
		$tpath1='images/series/'.$series_cover; 
		copy($pic1,$tpath1);

		$thumbpath='images/series/thumbs/'.$series_cover;
			
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
			    'series_name'  =>  $title,
			    'series_desc'  =>  $desc,
			   	'series_poster'  =>  $series_poster,
			   	'series_cover'  =>  $series_cover
			    );		

 		$qry = Insert('tbl_series',$data);			

		$_SESSION['msg']="10";
 
		header( "Location:manage_series.php");
		exit;	
		
	}
	
	if(isset($_GET['series_id']))
	{	 
		$qry="SELECT * FROM tbl_series where id='".$_GET['series_id']."'";
		$result=mysqli_query($mysqli,$qry);
		$row=mysqli_fetch_assoc($result);
	}
	if(isset($_POST['submit']) and isset($_POST['series_id']))
	{	
		$title=addslashes(trim($_POST['series_name']));
		$desc=addslashes(trim($_POST['series_desc']));
		
		if($_FILES['series_poster']['error']!=4){

			unlink('images/series/'.$row['series_poster']);
			unlink('images/series/thumbs/'.$row['series_poster']);

			// for series poster
			$series_poster=rand(0,99999)."_".$_FILES['series_poster']['name'];
			$pic1=$_FILES['series_poster']['tmp_name'];

						
			$tpath1='images/series/'.$series_poster; 
			copy($pic1,$tpath1);

			$thumbpath='images/series/thumbs/'.$series_poster;
				
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
			$series_poster=$row['series_poster'];
		}

		if($_FILES['series_cover']['error']!=4){
			unlink('images/series/'.$row['series_cover']);
			unlink('images/series/thumbs/'.$row['series_cover']);

			// for series cover
			$series_cover=rand(0,99999)."_".$_FILES['series_cover']['name'];
			$pic1=$_FILES['series_cover']['tmp_name'];

						
			$tpath1='images/series/'.$series_cover; 
			copy($pic1,$tpath1);

			$thumbpath='images/series/thumbs/'.$series_cover;
				
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
			$series_cover=$row['series_cover'];
		}

		$data = array( 
			    'series_name'  =>  $title,
			    'series_desc'  =>  $desc,
			   	'series_poster'  =>  $series_poster,
			   	'series_cover'  =>  $series_cover
			    );

		$edit=Update('tbl_series', $data, "WHERE id = '".$_POST['series_id']."'");

		$_SESSION['msg']="11"; 
		header( "Location:add_series.php?series_id=".$_POST['series_id']);
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
            <form action="" name="addeditcategory" method="post" class="form form-horizontal" enctype="multipart/form-data">
            	<input  type="hidden" name="series_id" value="<?php echo $_GET['series_id'];?>" />

              <div class="section">
                <div class="section-body">
                  <div class="form-group">
                    <label class="col-md-3 control-label">Series Name :-</label>
                    <div class="col-md-6">
                      <input type="text" name="series_name" id="series_name" value="<?php if(isset($_GET['series_id'])){echo $row['series_name'];}?>" class="form-control" required>
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-md-3 control-label">Poster Image:-
                      <p class="control-label-help" id="square_lable_info">(Recommended resolution: 185x278 portrait)</p>
                    </label>
                    <div class="col-md-6">
                      <div class="fileupload_block">
                        <input type="file" name="series_poster" value="" id="fileupload">
                        <div class="fileupload_img">
                        	<?php 
                        		$img_src="";

                        		if(!isset($_GET['series_id']) || !file_exists('images/series/'.$row['series_poster'])){
                        			$img_src='assets/images/series-poster.jpg';
                        		}else{
                        			$img_src='images/series/'.$row['series_poster'];
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
                        <input type="file" name="series_cover" value="" id="fileupload">
                        <div class="fileupload_img">
                        	<?php 
                        		$img_src="";

                        		if(!isset($_GET['series_id']) || !file_exists('images/series/'.$row['series_cover'])){
                        			$img_src='assets/images/series-cover.jpg';
                        		}else{
                        			$img_src='images/series/'.$row['series_cover'];
                        		}

                        	?>
                          <img type="image" src="<?=$img_src?>" alt="cover image" style="width: 150px;height: 86px" />
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="form-group">
                    <div class="col-md-3">
                      <label class="control-label">Series Description :-</label>
                    </div>
                    <div class="col-md-6">
                      <textarea name="series_desc" id="series_desc" rows="5" class="form-control"><?php if(isset($_GET['series_id'])){ echo $row['series_desc']; } ?></textarea>
                      <script>
                        CKEDITOR.replace('series_desc');
                      </script>
                    </div>
                  </div>
                  <br/>
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
	      $("input[name='series_poster']").next(".fileupload_img").find("img").attr('src', e.target.result);
	    }
	    
	    reader.readAsDataURL(input.files[0]);
	  }
	}
	$("input[name='series_poster']").change(function() { 
		readURL(this);
	});

	function readURL1(input) {
	  if (input.files && input.files[0]) {
	    var reader = new FileReader();
	    
	    reader.onload = function(e) {
	      $("input[name='series_cover']").next(".fileupload_img").find("img").attr('src', e.target.result);
	    }
	    
	    reader.readAsDataURL(input.files[0]);
	  }
	}
	$("input[name='series_cover']").change(function() { 
		readURL1(this);
	});
</script>    
