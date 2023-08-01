<?php 

  $page_title="Manage Channel";
  $active_page="channel";

  include("includes/header.php");
	require("includes/function.php");
	require("language/language.php");
	 
  if(isset($_POST["search"]))
  {
  	$channel_name=$_POST['search_value']; 
  	
    $qry="SELECT * FROM tbl_channels,tbl_category WHERE cat_id=cid and channel_title LIKE '%".$channel_name."%' ORDER BY tbl_channels.id DESC"; 
  	 
  }
  else if($_POST['cat_id']!=''){

    $cat_id=$_POST['cat_id'];
    $qry="SELECT tbl_channels.*,tbl_category.`category_name` FROM tbl_channels
          LEFT JOIN tbl_category ON tbl_channels.`cat_id`= tbl_category.`cid` WHERE tbl_channels.`cat_id`='$cat_id' ORDER BY tbl_channels.`id` DESC";  
  }
  else
  { 

    $tableName="tbl_channels";    
    $targetpage = "manage_channels.php";  
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

    $qry="SELECT tbl_channels.*,tbl_category.category_name FROM tbl_channels
          LEFT JOIN tbl_category ON tbl_channels.cat_id= tbl_category.cid ORDER BY tbl_channels.id DESC LIMIT $start, $limit";	  
  }
	$result=mysqli_query($mysqli,$qry);
 
	
  if(isset($_GET['channel_id']))
  {
  	$img_res=mysqli_query($mysqli,'SELECT * FROM tbl_channels WHERE id=\''.$_GET['channel_id'].'\'');
  	$img_res_row=mysqli_fetch_assoc($img_res);


  	if($img_res_row['channel_thumbnail']!="")
    {
      	unlink('images/'.$img_res_row['channel_thumbnail']);
  	  	unlink('images/thumbs/'.$img_res_row['channel_thumbnail']);

  	}

  	Delete('tbl_channels','id='.$_GET['channel_id'].'');

  	$_SESSION['msg']="12";
  	header( "Location:manage_channels.php");
  	exit;
  	 
  	
  }	


?>
    <div class="row">
      <div class="col-xs-12">
        <div class="card mrg_bottom">
          <div class="page_title_block">
            <div class="col-md-5 col-xs-12">
              <div class="page_title">Manage Channels</div>
            </div>
            <div class="col-md-7 col-xs-12">              
              <div class="search_list">
                <div class="search_block">
                  <form  method="post" action="">
                    <input class="form-control input-sm" placeholder="Search..." aria-controls="DataTables_Table_0" type="search" value="<?php if(isset($_POST['search_value'])){ echo $_POST['search_value']; } ?>" name="search_value" required>
                    <button type="submit" name="search" class="btn-search"><i class="fa fa-search"></i></button>
                  </form>  
                </div>
                <div class="add_btn_primary"> <a href="add_channel.php?add=yes">Add Channel</a> </div>
              </div>
            </div>
            <form method="post">
              <div class="col-md-8">
                <h4 style="float: left;">Filter: |</h4>
                <div class="search_list" style="padding: 0px 0px 5px;float: left;margin-left: 10px">
                  <select name="cat_id" class="form-control cat_id" style="padding: 5px 10px;height: 40px;">
                      <option value="">--Category--</option>
                      <?php 
                        $qry="SELECT * FROM tbl_category ORDER BY cid DESC";
                        $res=mysqli_query($mysqli, $qry) or die(mysqli_error($mysqli));
                        while ($info=mysqli_fetch_assoc($res)) {
                          ?>
                          <option value="<?=$info['cid']?>" <?php if(isset($_POST['cat_id']) && $_POST['cat_id']==$info['cid']){ echo 'selected';} ?>><?=$info['category_name']?></option>
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
                    <h2>
                      <?php
                        if(strlen($row['category_name']) > 14){
                          echo mb_substr(stripslashes($row['category_name']), 0, 14).'...';  
                        }else{
                          echo $row['category_name'];
                        }
                      ?>
                    </h2>
                    <?php if($row['slider_channel']!="0"){?>
                       <a class="toggle_btn_a" data-id="<?=$row['id']?>" data-action="deactive" data-column="slider_channel" data-toggle="tooltip" data-tooltip="Slider" style="margin-left: 5px"><div style="color:green;"><i class="fa fa-sliders"></i></div></a> 
                    <?php }else{?>
                       <a class="toggle_btn_a" data-id="<?=$row['id']?>" data-action="active" data-column="slider_channel" data-toggle="tooltip" data-tooltip="Set Slider" style="margin-left: 5px"><i class="fa fa-sliders"></i></a> 
                    <?php }?>
                    <div class="checkbox" style="float: right;">
                        <input type="checkbox" name="post_ids[]" id="checkbox<?php echo $i;?>" value="<?php echo $row['id']; ?>" class="post_ids">
                        <label for="checkbox<?php echo $i;?>">
                        </label>
                    </div>            
                  </div>
                  <div class="wall_image_title">
                     <p><?php echo $row['channel_title'];?></p>
                    <ul>
                      <li><a href="javascript:void(0)" data-toggle="tooltip" data-tooltip="<?php echo $row['total_views'];?> Views"><i class="fa fa-eye"></i></a></li>
                      <li><a href="channel_ratings.php?channel_id=<?php echo $row['id'];?>" data-toggle="tooltip" data-tooltip="View Rates"><i class="fa fa-star"></i></a></li> 
                      <li><a href="edit_channel.php?channel_id=<?php echo $row['id'];?>" target="_blank" data-toggle="tooltip" data-tooltip="Edit"><i class="fa fa-edit"></i></a></li>
                      <li><a href="?channel_id=<?php echo $row['id'];?>" data-toggle="tooltip" data-tooltip="Delete"><i class="fa fa-trash"></i></a></li>

                      <?php if($row['status']!="0"){?>
                      <li><div class="row toggle_btn"><a href="javascript:void(0)" data-id="<?php echo $row['id'];?>" data-action="deactive" data-column="status" data-toggle="tooltip" data-tooltip="ENABLE"><img src="assets/images/btn_enabled.png" alt="wallpaper_1" /></a></div></li>

                      <?php }else{?>
                      
                      <li><div class="row toggle_btn"><a href="javascript:void(0)" data-id="<?=$row['id']?>" data-action="active" data-column="status" data-toggle="tooltip" data-tooltip="DISABLE"><img src="assets/images/btn_disabled.png" alt="wallpaper_1" /></a></div></li>
                  
                      <?php }?>
                    <ul>  
                  </div>
                  <span><img src="images/<?php echo $row['channel_thumbnail'];?>" /></span>
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
    var _table='tbl_channels';

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
    var _table='tbl_channels';

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
        if(confirm("Are you sure you want to delete this channel?")){
          $.ajax({
            type:'post',
          url:'processData.php',
            dataType:'json',
            data:{id:_ids,'action':'multi_delete','tbl_nm':'tbl_channels'},
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
        alert("No channel selected");
      }
  });


  $("button[name='delete_rec']").click(function(e){
      e.preventDefault();

      var _ids = $.map($('.post_ids:checked'), function(c){return c.value; });

      if(_ids!='')
      {
        if(confirm("Are you sure you want to delete this channels?")){
          $.ajax({
            type:'post',
          url:'processData.php',
            dataType:'json',
            data:{id:_ids,'action':'multi_delete','tbl_nm':'tbl_channels'},
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
        alert("No channel selected");
      }
  });

</script>

<script type="text/javascript">
  $("#checkall").click(function () {
    $('input:checkbox').not(this).prop('checked', this.checked);
  });
</script>