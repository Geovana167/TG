<?php 

  $page_title="Manage Movies";
  $active_page="movies";

  include("includes/header.php");
	require("includes/function.php");
	require("language/language.php");
	 
  if(isset($_POST["search"]))
  {
  	$search_txt=addslashes(trim($_POST['search_value'])); 

    $sql="SELECT tbl_language.`language_name`,tbl_movies.* FROM tbl_movies
          LEFT JOIN tbl_language ON tbl_movies.language_id= tbl_language.id 
          WHERE (tbl_movies.`movie_title` LIKE '%$search_txt%' OR tbl_language.`language_name` LIKE '%$search_txt%')
          ORDER BY tbl_movies.id DESC";
  	 
  }
  else if($_POST['lang_id']!=''  && $_POST['genre_id']=='')
  {
      $lang_id=$_POST['lang_id'];
      $sql="SELECT tbl_language.`language_name`,tbl_movies.* FROM tbl_movies
          LEFT JOIN tbl_language ON tbl_movies.`language_id`= tbl_language.`id` 
          WHERE tbl_movies.`language_id`='$lang_id'
          ORDER BY tbl_movies.`id` DESC";
  }
  else if($_POST['lang_id']!=''  && $_POST['genre_id']!='')
  {
      $lang_id=$_POST['lang_id'];
      $genre_id=$_POST['genre_id'];

      $sql="SELECT tbl_language.`language_name`,tbl_movies.* FROM tbl_movies
          LEFT JOIN tbl_language ON tbl_movies.`language_id`= tbl_language.`id` 
          WHERE tbl_movies.`language_id`='$lang_id' AND FIND_IN_SET(tbl_movies.`genre_id`,$genre_id)
          ORDER BY tbl_movies.`id` DESC";
  }
  else if($_POST['lang_id']==''  && $_POST['genre_id']!='')
  {
      $genre_id=$_POST['genre_id'];
      $sql="SELECT tbl_language.`language_name`,tbl_movies.* FROM tbl_movies
          LEFT JOIN tbl_language ON tbl_movies.`language_id`= tbl_language.`id` 
          WHERE FIND_IN_SET(tbl_movies.`genre_id`,$genre_id)
          ORDER BY tbl_movies.`id` DESC";
  }
  else
  { 

    $tableName="tbl_movies";    
    $targetpage = "manage_movies.php";  
    $limit = 2; 
    
    $query = "SELECT COUNT(*) as num FROM $tableName";
    $total_pages = mysqli_fetch_array(mysqli_query($mysqli,$query));
    $total_pages = $total_pages['num'];
    
    $stages = 3;
    $page=0;
    if(isset($_GET['page'])){
    $page = mysqli_real_escape_string($mysqli,$_GET['page']);
    }
    if($page){
      $start = ($page - 1) * $limit; 
    }else{
      $start = 0; 
    } 

    $sql="SELECT tbl_language.language_name,tbl_movies.* FROM tbl_movies
          LEFT JOIN tbl_language ON tbl_movies.language_id= tbl_language.id 
          ORDER BY tbl_movies.id DESC LIMIT $start, $limit";
  }

	 $result=mysqli_query($mysqli,$sql);
 
	
  if(isset($_GET['movie_id']))
  {
  	
    $id=$_GET['movie_id'];

    $res_movie=mysqli_query($mysqli,"SELECT * FROM tbl_movies WHERE `id`='$id'");
    $row=mysqli_fetch_assoc($res_movie);

    if($row['movie_poster']!="")
    {
      unlink('images/movies/'.$row['movie_poster']);
      unlink('images/movies/thumbs/'.$row['movie_poster']);
    }

    if($row['movie_cover']!="")
    {
      unlink('images/movies/'.$row['movie_cover']);
      unlink('images/movies/thumbs/'.$row['movie_cover']);
    }

    Delete('tbl_movies','id='.$id.'');
     
  	$_SESSION['msg']="12";
  	header( "Location:manage_movies.php");
  	exit;
  	 
  	
  }
	 
?>
    <div class="row">
      <div class="col-xs-12">
        <div class="card mrg_bottom">
          <div class="page_title_block">
            <div class="col-md-5 col-xs-12">
              <div class="page_title"><?=$page_title?></div>
            </div>
            <div class="col-md-7 col-xs-12">              
              <div class="search_list">
                <div class="search_block">
                  <form method="post" action="">
                    <input class="form-control input-sm" placeholder="Search..." aria-controls="DataTables_Table_0" type="search" name="search_value" value="<?php if(isset($_POST['search_value'])){ echo $_POST['search_value']; }?>" required>
                    <button type="submit" name="search" class="btn-search"><i class="fa fa-search"></i></button>
                  </form>  
                </div>
                <div class="add_btn_primary"> <a href="add_movie.php?add=yes">Add Movie</a> </div>
              </div>
            </div>
            <form method="post">
              <div class="col-md-8">
                <h4 style="float: left;">Filter: |</h4>
                <div class="search_list" style="padding: 0px 0px 5px;float: left;margin-left: 10px">
                  <select name="lang_id" class="form-control lang_id" style="padding: 5px 10px;height: 40px;">
                      <option value="">--Language--</option>
                      <?php 
                        $qry="SELECT * FROM tbl_language ORDER BY id DESC";
                        $res=mysqli_query($mysqli, $qry) or die(mysqli_error($mysqli));
                        while ($info=mysqli_fetch_assoc($res)) {
                          ?>
                          <option value="<?=$info['id']?>" <?php if(isset($_POST['lang_id']) && $_POST['lang_id']==$info['id']){ echo 'selected';} ?>><?=$info['language_name']?></option>
                          <?php
                        }
                        mysqli_free_result($res);
                      ?>
                    </select>
                </div>
                <div class="search_list" style="padding: 0px 0px 5px;float: left;margin-left: 20px">
                    <select name="genre_id" class="form-control" style="padding: 5px 10px;height: 40px;">
                      <option value="">--Genre--</option>
                      <?php 
                        $qry="SELECT * FROM tbl_genres ORDER BY gid DESC";
                        $res=mysqli_query($mysqli, $qry) or die(mysqli_error($mysqli));
                        while ($info=mysqli_fetch_assoc($res)) {
                          ?>
                          <option value="<?=$info['gid']?>" <?php if(isset($_POST['genre_id']) && $_POST['genre_id']==$info['gid']){ echo 'selected';} ?>><?=$info['genre_name']?></option>
                          <?php
                        }
                        mysqli_free_result($res);
                      ?>
                    </select>
                    <button class="btn btn-primary" style="padding: 5px 10px;height: 40px;margin-left: 10px;transform: none !important;"><i class="fa fa-filter"></i> Filter</button>
                </div>
              </div>
            </form>
            <div class="col-md-3 col-xs-12" style="float: right;width: 18%">
                <div class="checkbox">
                  <input type="checkbox" name="checkall" id="checkall" value="">
                  <label for="checkall">Select All</label>
                  <button type="submit" class="btn btn-danger btn_delete" name="delete_rec" value="delete_wall">Delete</button>
                </div> 
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
          <div class="col-md-12 mrg-top">
            <div class="row">
              <?php 
                $i=0;
                while($row=mysqli_fetch_array($result))
                {         
              ?>
              <div class="col-lg-3 col-sm-6 col-xs-12">
                <div class="block_wallpaper">
                  <div class="wall_category_block">
                    <h2><?php echo $row['language_name'];?></h2>  
                    <?php if($row['is_slider']!="0"){?>
                       <a class="toggle_btn_a" data-id="<?=$row['id']?>" data-action="deactive" data-column="is_slider" data-toggle="tooltip" data-tooltip="Slider" style="margin-left: 5px"><div style="color:green;"><i class="fa fa-sliders"></i></div></a> 
                    <?php }else{?>
                       <a class="toggle_btn_a" data-id="<?=$row['id']?>" data-action="active" data-column="is_slider" data-toggle="tooltip" data-tooltip="Set Slider" style="margin-left: 5px"><i class="fa fa-sliders"></i></a> 
                    <?php }?>

                    <div class="checkbox" style="float: right;">
                        <input type="checkbox" name="post_ids[]" id="checkbox<?php echo $i;?>" value="<?php echo $row['id']; ?>" class="post_ids">
                        <label for="checkbox<?php echo $i;?>">
                        </label>
                    </div>

                  </div>
                  <div class="wall_image_title" style="background: rgba(0,0,0,0.3);">
                    <p style="text-shadow: 0px 1px 1px #000">
                      <?php
                        if(strlen($row['movie_title']) > 25){
                          echo mb_substr(stripslashes($row['movie_title']), 0, 25).'...';  
                        }else{
                          echo $row['movie_title'];
                        }
                      ?>
                        
                    </p>
                    <ul style="z-index: 1">
                      <li><a href="javascript:void(0)" data-toggle="tooltip" data-tooltip="<?php echo $row['total_views'];?> Views"><i class="fa fa-eye"></i></a></li>                      
                      
                       
                      
                      <li><a href="" data-toggle="tooltip" data-tooltip="View Rates"><i class="fa fa-star"></i></a></li>
                        
                      <li><a href="add_movie.php?movie_id=<?php echo $row['id'];?>" target="_blank" data-toggle="tooltip" data-tooltip="Edit"><i class="fa fa-edit"></i></a></li>
                      <li><a href="" class="btn_delete_a" data-id="<?php echo $row['id'];?>" data-toggle="tooltip" data-tooltip="Delete"><i class="fa fa-trash"></i></a></li>

                      <?php if($row['status']!="0"){?>
                      <li><div class="row toggle_btn"><a href="javascript:void(0)" data-id="<?php echo $row['id'];?>" data-action="deactive" data-column="status" data-toggle="tooltip" data-tooltip="ENABLE"><img src="assets/images/btn_enabled.png" alt="wallpaper_1" /></a></div></li>

                      <?php }else{?>
                      
                      <li><div class="row toggle_btn"><a href="javascript:void(0)" data-id="<?=$row['id']?>" data-action="active" data-column="status" data-toggle="tooltip" data-tooltip="DISABLE"><img src="assets/images/btn_disabled.png" alt="wallpaper_1" /></a></div></li>
                  
                      <?php }?>
                    <ul>  
                  </div>
                  <span><img src="images/movies/<?php echo $row['movie_cover'];?>" /></span>
                </div>
              </div>
          <?php
            
            $i++;
              }
        ?>     
         
       
      </div>
          </div>
          <div class="col-md-12 col-xs-12">
            <div class="pagination_item_block">
              <nav>
              	<?php if(!isset($_POST["search"])){ include("pagination.php");}?>                 
              </nav>
            </div>
          </div>
          <div class="clearfix"></div>
        </div>
      </div>
    </div>            
               
        
<?php include("includes/footer.php");?>       

<script type="text/javascript">

  $(".toggle_btn a").on("click",function(e){
    e.preventDefault();
    var _cur_element=$(this);

    var _cur_img=$(this).find("img");
    var _cur_img_src=$(this).find("img").attr("src");
    var _flag=false;
    var _img_src='';
    var _img_tooltip='';

    if(_cur_img_src=='assets/images/btn_disabled.png'){
      _flag=true;
      _img_src='assets/images/btn_enabled.png';
      _img_tooltip='ENABLE';
    }else{
      _flag=true;
      _img_src='assets/images/btn_disabled.png';
      _img_tooltip='DISABLE';
    }

    var _for=$(this).data("action");
    var _id=$(this).data("id");
    var _column=$(this).data("column");
    var _table='tbl_movies';

    $.ajax({
      type:'post',
      url:'processData.php',
      dataType:'json',
      data:{id:_id,for_action:_for,column:_column,table:_table,'action':'toggle_status','tbl_id':'id'},
      success:function(res){
          console.log(res);
          if(res.status=='1'){
            if(_flag){
              _cur_img.attr('src',_img_src);
              _cur_element.attr("data-tooltip",_img_tooltip);  
            }
            
          }
        }
    });

  });

  $(".toggle_btn_a").on("click",function(e){
    e.preventDefault();

    var _for=$(this).data("action");
    var _id=$(this).data("id");
    var _column=$(this).data("column");
    var _table='tbl_movies';

    $.ajax({
      type:'post',
      url:'processData.php',
      dataType:'json',
      data:{id:_id,for_action:_for,column:_column,table:_table,'action':'toggle_status','tbl_id':'id'},
      success:function(res){
          console.log(res);
          if(res.status=='1'){
            location.reload();
          }
        }
    });

  });

  $(".btn_delete_a").click(function(e){
      e.preventDefault();

      var _ids = $(this).data("id");

      if(_ids!='')
      {
        if(confirm("Are you sure you want to delete this movie?")){
          $.ajax({
            type:'post',
          url:'processData.php',
            dataType:'json',
            data:{id:_ids,'action':'multi_delete','tbl_nm':'tbl_movies'},
            success:function(res){
                console.log(res);
                if(res.status=='1'){
                  location.reload();
                }
                else if(res.status=='-2'){
                  alert(res.message);
                }
              }
          });
        }
      }
      else{
        alert("No movie selected");
      }
  });


  $("button[name='delete_rec']").click(function(e){
      e.preventDefault();

      var _ids = $.map($('.post_ids:checked'), function(c){return c.value; });

      if(_ids!='')
      {
        if(confirm("Are you sure you want to delete this movies?")){
          $.ajax({
            type:'post',
          url:'processData.php',
            dataType:'json',
            data:{id:_ids,'action':'multi_delete','tbl_nm':'tbl_movies'},
            success:function(res){
                console.log(res);
                if(res.status=='1'){
                  location.reload();
                }
                else if(res.status=='-2'){
                  alert(res.message);
                }
              }
          });
        }
      }
      else{
        alert("No quotes selected");
      }
  });

</script>

<script type="text/javascript">
  $("#checkall").click(function () {
    $('input:checkbox').not(this).prop('checked', this.checked);
  });
</script> 
