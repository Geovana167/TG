<?php 
  
  $page_title="Manage Episodes";
  $active_page="series";

  include("includes/header.php");
	require("includes/function.php");
	require("language/language.php");

  if(isset($_POST['data_search']))
  {

      $search_txt=addslashes(trim($_POST['search_value'])); 

      $sql="SELECT episode.*, series.`series_name`, season.`season_name`  FROM tbl_episode episode
          LEFT JOIN tbl_series series ON episode.`series_id`=series.`id`
          LEFT JOIN tbl_season season ON episode.`season_id`=season.`id`
          WHERE (season.`season_name` LIKE '%$search_txt%' OR episode.`episode_title` LIKE '%$search_txt%' OR series.`series_name` LIKE '%$search_txt%')
          ORDER BY episode.`id` DESC";

      

   }
   else if($_POST['series_id']!=''  && $_POST['season_id']==''){

      $series_id=$_POST['series_id'];

      $sql="SELECT episode.*, series.`series_name`, season.`season_name`  FROM tbl_episode episode
            LEFT JOIN tbl_series series ON episode.`series_id`=series.`id`
            LEFT JOIN tbl_season season ON episode.`season_id`=season.`id`
            WHERE episode.`series_id`='$series_id'
            ORDER BY episode.`id` DESC";
   }
   else if($_POST['series_id']!=''  && $_POST['season_id']!=''){

      $series_id=$_POST['series_id'];
      $season_id=$_POST['season_id'];

      $sql="SELECT episode.*, series.`series_name`, season.`season_name`  FROM tbl_episode episode
            LEFT JOIN tbl_series series ON episode.`series_id`=series.`id`
            LEFT JOIN tbl_season season ON episode.`season_id`=season.`id`
            WHERE episode.`series_id`='$series_id' AND episode.`season_id`='$season_id'
            ORDER BY episode.`id` DESC";
   }
   else
   {
	
	//Get all Category 
	 
      $tableName="tbl_episode";   
      $targetpage = "manage_episode.php"; 
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
      
     
     $sql="SELECT episode.*, series.`series_name`, season.`season_name`  FROM tbl_episode episode
            LEFT JOIN tbl_series series ON episode.`series_id`=series.`id`
            LEFT JOIN tbl_season season ON episode.`season_id`=season.`id`
            ORDER BY episode.`id` DESC LIMIT $start, $limit
            ";
 
     
	
    } 

    $result=mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli));

	if(isset($_GET['episode_id']))
	{

      $id=$_GET['episode_id'];

      $res_episode=mysqli_query($mysqli,"SELECT * FROM tbl_episode WHERE `id`='$id'");
      $row=mysqli_fetch_assoc($res_episode);

      if($row['episode_poster']!="")
      {
        unlink('images/episodes/'.$row['episode_poster']);
        unlink('images/episodes/thumbs/'.$row['episode_poster']);
      }

      if($row['episode_type']=='local'){
        unlink('uploads/'.$row['episode_url']);
      }

      Delete('tbl_episode','id='.$id.'');
       
      $_SESSION['msg']="12";
      header( "Location:manage_episode.php");
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
                        <button type="submit" name="data_search" class="btn-search"><i class="fa fa-search"></i></button>
                  </form>  
                </div>
                <div class="add_btn_primary"> <a href="add_episode.php?add=yes">Add Episode</a> </div>
              </div>
            </div>
            <form method="post">
              <div class="col-md-8">
                <h4 style="float: left;">Filter: |</h4>
                <div class="search_list" style="padding: 0px 0px 5px;float: left;margin-left: 10px">
                  <select name="series_id" class="form-control series_id" required style="padding: 5px 10px;height: 40px;">
                      <option value="">--Series--</option>
                      <?php 
                        $qry="SELECT * FROM tbl_series ORDER BY id DESC";
                        $res=mysqli_query($mysqli, $qry) or die(mysqli_error($mysqli));
                        while ($info=mysqli_fetch_assoc($res)) {
                          ?>
                          <option value="<?=$info['id']?>" <?php if(isset($_POST['series_id']) && $_POST['series_id']==$info['id']){ echo 'selected';} ?>><?=$info['series_name']?></option>
                          <?php
                        }
                      ?>
                    </select>
                </div>
                <div class="search_list" style="padding: 0px 0px 5px;float: left;margin-left: 20px">
                    <input type="hidden" class="old_season_id" value="<?php if(isset($_POST['season_id'])){ echo $_POST['season_id'];}?>">
                    <select name="season_id" class="form-control" style="padding: 5px 10px;height: 40px;">
                      <option value="">--Season--</option>
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
                    <h2><?php echo $row['series_name'];?></h2>  
                    <div class="checkbox" style="float: right;">
                        <input type="checkbox" name="post_ids[]" id="checkbox<?php echo $i;?>" value="<?php echo $row['id']; ?>" class="post_ids">
                        <label for="checkbox<?php echo $i;?>">
                        </label>
                    </div>
                  </div>
                  <div class="wall_image_title">
                    <b style="font-size: 15px;font-weight: 600"><?=$row['season_name']?></b>
                    <p style="text-shadow: 0px 1px 1px #000">
                      <?php
                        if(strlen($row['episode_title']) > 25){
                          echo mb_substr(stripslashes($row['episode_title']), 0, 25).'...';  
                        }else{
                          echo $row['episode_title'];
                        }
                        ?>
                    </p>
                    
                    <ul style="z-index: 1">
                        
                      <li><a href="add_episode.php?episode_id=<?php echo $row['id'];?>" target="_blank" data-toggle="tooltip" data-tooltip="Edit"><i class="fa fa-edit"></i></a></li>
                      <li><a href="" class="btn_delete_a" data-id="<?php echo $row['id'];?>" data-toggle="tooltip" data-tooltip="Delete"><i class="fa fa-trash"></i></a></li>

                      <?php if($row['status']!="0"){?>
                      <li><div class="row toggle_btn"><a href="javascript:void(0)" data-id="<?php echo $row['id'];?>" data-action="deactive" data-column="status" data-toggle="tooltip" data-tooltip="ENABLE"><img src="assets/images/btn_enabled.png" alt="wallpaper_1" /></a></div></li>

                      <?php }else{?>
                      
                      <li><div class="row toggle_btn"><a href="javascript:void(0)" data-id="<?=$row['id']?>" data-action="active" data-column="status" data-toggle="tooltip" data-tooltip="DISABLE"><img src="assets/images/btn_disabled.png" alt="wallpaper_1" /></a></div></li>
                  
                      <?php }?>
                    <ul>  
                  </div>
                  <span><img src="images/episodes/<?php echo $row['episode_poster'];?>" /></span>
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
                <?php if(!isset($_POST["data_search"])){ include("pagination.php");}?>
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
    var _table='tbl_episode';

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

  $(".btn_delete_a").click(function(e){
      e.preventDefault();

      var _ids = $(this).data("id");

      if(_ids!='')
      {
        if(confirm("Are you sure you want to delete this episode?")){
          $.ajax({
            type:'post',
            url:'processData.php',
            dataType:'json',
            data:{id:_ids,'action':'multi_delete','tbl_nm':'tbl_episode'},
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
        alert("No episode selected");
      }
  });


  $("button[name='delete_rec']").click(function(e){
      e.preventDefault();

      var _ids = $.map($('.post_ids:checked'), function(c){return c.value; });

      if(_ids!='')
      {
        if(confirm("Are you sure you want to delete this episodes?")){
          $.ajax({
            type:'post',
          url:'processData.php',
            dataType:'json',
            data:{id:_ids,'action':'multi_delete','tbl_nm':'tbl_episode'},
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
        alert("No episode selected");
      }
  });


  $("select[name='series_id']").on("change",function(e){
    $("select[name='season_id']").html('<option value="">--Season--</option>');
    var _id=$(this).val();

    if(_id!=''){
      $.ajax({
        type:'post',
        url:'processData.php',
        dataType:'json',
        data:{id:_id,'action':'getSeason'},
        success:function(res){
            console.log(res);
            if(res.status=='1'){
              $.each(res.data, function(index, item) {
                  $("select[name='season_id']").append(item);
              });
            }
          }
      }); 
    }

  });

  var _id=$("select[name='series_id']").val();
  if(_id!=''){
    var _old_id=$(".old_season_id").val();
    $.ajax({
      type:'post',
      url:'processData.php',
      dataType:'json',
      data:{id:_id,'action':'getSeason'},
      success:function(res){
          console.log(res);
          if(res.status=='1'){
            $.each(res.data, function(index, item) {
                $("select[name='season_id']").append(item);

            });
            $('select[name="season_id"] option[value="'+_old_id+'"]').prop('selected', true);
          }
        }
    });
  }

</script>

<script type="text/javascript">
  $("#checkall").click(function () {
    $('input:checkbox').not(this).prop('checked', this.checked);
  });
</script> 