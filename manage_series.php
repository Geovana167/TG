<?php 
  
  $page_title="Manage TV Series";
  $active_page="series";

  include("includes/header.php");
	require("includes/function.php");
	require("language/language.php");

  if(isset($_POST['search']))
  {

      $search_txt=addslashes(trim($_POST['search_value'])); 

      $sql="SELECT * FROM tbl_series WHERE (tbl_series.`series_name` LIKE '%$search_txt%') ORDER BY tbl_series.id DESC";

      $result=mysqli_query($mysqli,$sql); 

  }
   else
   {
	 
      $tableName="tbl_series";   
      $targetpage = "manage_series.php"; 
      $limit = 12; 
      
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
      
     $qry="SELECT * FROM tbl_series ORDER BY tbl_series.id DESC LIMIT $start, $limit";
 
     $result=mysqli_query($mysqli,$qry); 
	
    } 

	 if(isset($_GET['series_id']))
	 {
      $id=$_GET['series_id'];
      Delete('tbl_season','series_id='.$id.'');
    
      $res_episode=mysqli_query($mysqli,"SELECT * FROM tbl_episode WHERE `series_id`='$id'");
		  $row=mysqli_fetch_assoc($res_episode);

		  if($row['episode_poster']!="")
	    {
	    	unlink('images/episodes/'.$row['episode_poster']);
			  unlink('images/episodes/thumbs/'.$row['episode_poster']);
		  }

      if($row['episode_type']=='local'){
        unlink('uploads/'.$row['episode_url']);
      }
 
		  Delete('tbl_episode','series_id='.$id.'');

      $res_series=mysqli_query($mysqli,"SELECT * FROM tbl_series WHERE `id`='$id'");
      $row=mysqli_fetch_assoc($res_series);

      if($row['series_poster']!="")
      {
        unlink('images/series/'.$row['series_poster']);
        unlink('images/series/thumbs/'.$row['series_poster']);
      }

      if($row['series_cover']!="")
      {
        unlink('images/series/'.$row['series_cover']);
        unlink('images/series/thumbs/'.$row['series_cover']);
      }
 
      Delete('tbl_series','id='.$id.'');
     
		  $_SESSION['msg']="12";
		  header( "Location:manage_series.php");
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
                  <form  method="post" action="">
                  <input class="form-control input-sm" placeholder="Search..." aria-controls="DataTables_Table_0" type="search" name="search_value" value="<?php if(isset($_POST['search_value'])){ echo $_POST['search_value']; }?>" required>
                        <button type="submit" name="search" class="btn-search"><i class="fa fa-search"></i></button>
                  </form>  
                </div>
                <div class="add_btn_primary"> <a href="add_series.php?add=yes">Add Series</a> </div>
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
                <div class="block_wallpaper add_wall_category">           
                  <div class="wall_image_title">
                    <h2><a href="javascript:void(0)" style="text-shadow: 1px 1px 1px #000"><?php echo $row['series_name'];?></a></h2>
                    <ul> 
                      <li><a href="javascript:void(0)" data-toggle="tooltip" data-tooltip="<?php echo $row['total_views'];?> Views"><i class="fa fa-eye"></i></a></li>  
                      
                      <?php if($row['is_slider']!="0"){?>
                        <li><a class="toggle_btn_a" data-id="<?=$row['id']?>" data-action="deactive" data-column="is_slider" data-toggle="tooltip" data-tooltip="Slider" style="margin-left: 5px"><div style="color:green;"><i class="fa fa-sliders"></i></div></a>
                        </li>
                      <?php }else{?>
                        <li>
                          <a class="toggle_btn_a" data-id="<?=$row['id']?>" data-action="active" data-column="is_slider" data-toggle="tooltip" data-tooltip="Set Slider" style="margin-left: 5px"><i class="fa fa-sliders"></i>
                          </a> 
                        </li>
                      <?php }?>

                      <li><a href="add_series.php?series_id=<?php echo $row['id'];?>" data-toggle="tooltip" data-tooltip="Edit"><i class="fa fa-edit"></i></a></li>               
                      <li><a href="?series_id=<?php echo $row['id'];?>" data-toggle="tooltip" data-tooltip="Delete"><i class="fa fa-trash"></i></a></li>
                      
                      <?php if($row['status']!="0"){?>
                      <li><div class="row toggle_btn"><a href="javascript:void(0)" data-id="<?php echo $row['id'];?>" data-action="deactive" data-column="status" data-toggle="tooltip" data-tooltip="ENABLE"><img src="assets/images/btn_enabled.png" alt="wallpaper_1" /></a></div></li>

                      <?php }else{?>
                      
                      <li><div class="row toggle_btn"><a href="javascript:void(0)" data-id="<?=$row['id']?>" data-action="active" data-column="status" data-toggle="tooltip" data-tooltip="DISABLE"><img src="assets/images/btn_disabled.png" alt="wallpaper_1" /></a></div></li>
                  
                      <?php }?>


                    </ul>
                  </div>
                  <span><img src="images/series/<?php echo $row['series_cover'];?>" /></span>
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
                <?php if(!isset($_POST["search_value"])){ include("pagination.php");}?>
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
    var _img_src='';
    var _img_tooltip='';

    if(_cur_img_src=='assets/images/btn_disabled.png'){
      _img_src='assets/images/btn_enabled.png';
      _img_tooltip='ENABLE';
    }else{
      _img_src='assets/images/btn_disabled.png';
      _img_tooltip='DISABLE';
    }

    var _for=$(this).data("action");
    var _id=$(this).data("id");
    var _column=$(this).data("column");
    var _table='tbl_series';

    $.ajax({
      type:'post',
      url:'processData.php',
      dataType:'json',
      data:{id:_id,for_action:_for,column:_column,table:_table,'action':'toggle_status','tbl_id':'id'},
      success:function(res){
          console.log(res);
          if(res.status=='1'){
            _cur_img.attr('src',_img_src);
            _cur_element.attr("data-tooltip",_img_tooltip);
          }
        }
    });

  });

  $(".toggle_btn_a").on("click",function(e){
    e.preventDefault();

    var _for=$(this).data("action");
    var _id=$(this).data("id");
    var _column=$(this).data("column");
    var _table='tbl_series';

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

</script>     
