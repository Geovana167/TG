<?php include("includes/header.php");

  require("includes/function.php");
  require("language/language.php");

  function get_post($post_id,$type)
  {
    global $mysqli;

    switch ($type) {
      case 'series':
        $sql="SELECT * FROM tbl_series where id='$post_id' AND status='1'";
        break;

      case 'movie':
        $sql="SELECT * FROM tbl_movies where id='$post_id' AND status='1'";
        break;

      case 'channel':
        $sql="SELECT * FROM tbl_channels where id='$post_id' AND status='1'";
        break;
      
      default:
        break;
    }

    $res=mysqli_query($mysqli,$sql);
    $data=mysqli_fetch_assoc($res);

    return $data;
  }
  
  function get_user_info($user_id)
   {
    global $mysqli;

     
    $user_qry="SELECT * FROM tbl_users where id='".$user_id."'";
    $user_result=mysqli_query($mysqli,$user_qry);
    $user_row=mysqli_fetch_assoc($user_result);

    return $user_row;
   }
    
  // Get page data
  $tableName="tbl_reports";    
  $targetpage = "manage_reports.php";  
  $limit = 15; 
  
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


    
 
  
  if(isset($_GET['report_id']))
  {
    Delete('tbl_reports','id='.$_GET['report_id'].'');
    $_SESSION['msg']="12";
    header( "Location:manage_reports.php");
    exit;
     
  } 
   
?>
    <div class="row">
      <div class="col-xs-12">
        <div class="card mrg_bottom">
          <div class="page_title_block">
            <div class="col-md-5 col-xs-12">
              <div class="page_title">Manage Reports</div>
            </div>
             
          </div>
          <div class="clearfix"></div>
          <div class="row mrg-top">
            <div class="col-md-12">
               
              <div class="col-md-12 col-sm-12">
                <?php if(isset($_SESSION['msg'])){?> 
               	 <div class="alert alert-success alert-dismissible" role="alert"> <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                  <?php echo $client_lang[$_SESSION['msg']] ; ?></div>
                  <?php unset($_SESSION['msg']);}?>	
              </div>
            </div>
          </div>
          <div class="col-md-12 mrg-top">
            <ul class="nav nav-tabs" role="tablist">
                <li role="presentation" class="active"><a href="#series_report" aria-controls="series_report" role="tab" data-toggle="tab">Series Report</a></li>
                <li role="presentation"><a href="#movie_report" aria-controls="movie_report" role="tab" data-toggle="tab">Movie Report</a></li>
                <li role="presentation"><a href="#channel_report" aria-controls="channel_report" role="tab" data-toggle="tab">Channel Report</a></li>
            </ul>
          
            <div class="tab-content">
              <div role="tabpanel" class="tab-pane active" id="series_report">
                <table class="table table-striped table-bordered table-hover">
                  <thead>
                    <tr>
                      <th>Name</th>
                      <th>Email</th>
                      <th>Series</th>
                      <th>Report</th> 
                      <th class="cat_action_list">Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                      $qry="SELECT * FROM tbl_reports WHERE tbl_reports.`type`='series' ORDER BY tbl_reports.`id` DESC LIMIT $start, $limit";   
                      $result=mysqli_query($mysqli,$qry);
                      $i=0;
                      while($row=mysqli_fetch_array($result))
                      {
                    ?>
                    <tr>
                      <td><?php echo get_user_info($row['user_id'])['name'];?></td>
                      <td><?php echo get_user_info($row['user_id'])['email'];?></td>
                      <td><?php echo get_post($row['post_id'],$row['type'])['series_name'];?></td>
                      <td><?php echo $row['report'];?></td>                  
                      <td>
                        <a href="manage_reports.php?report_id=<?php echo $row['id'];?>" onclick="return confirm('Are you sure you want to delete this report?');" class="btn btn-danger btn_delete"><i class="fa fa-trash"></i> Delete</a>
                      </td>
                    </tr>
                    <?php
                      $i++;
                      }
                    ?>
                  </tbody>
                </table>
              </div>

              <div role="tabpanel" class="tab-pane" id="movie_report">
                <table class="table table-striped table-bordered table-hover">
                  <thead>
                    <tr>
                      <th>Name</th>
                      <th>Email</th>
                      <th class="cat_action_list">Movie</th>
                      <th>Report</th> 
                      <th class="cat_action_list">Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                      $qry="SELECT * FROM tbl_reports WHERE tbl_reports.`type`='movie' ORDER BY tbl_reports.`id` DESC LIMIT $start, $limit";   
                      $result=mysqli_query($mysqli,$qry);
                      $i=0;
                      while($row=mysqli_fetch_array($result))
                      {
                    ?>
                    <tr>
                      <td><?php echo get_user_info($row['user_id'])['name'];?></td>
                      <td><?php echo get_user_info($row['user_id'])['email'];?></td>
                      <td class="cat_action_list"><?php echo get_post($row['post_id'],$row['type'])['movie_title'];?></td>
                      <td><?php echo $row['report'];?></td>                  
                      <td>
                        <a href="manage_reports.php?report_id=<?php echo $row['id'];?>" onclick="return confirm('Are you sure you want to delete this report ?');" class="btn btn-danger btn_delete"><i class="fa fa-trash"></i> Delete</a>
                      </td>
                    </tr>
                    <?php
                      $i++;
                      }
                    ?>
                  </tbody>
                </table>
              </div>

              <div role="tabpanel" class="tab-pane" id="channel_report">
                <table class="table table-striped table-bordered table-hover">
                  <thead>
                    <tr>
                      <th>Name</th>
                      <th>Email</th>
                      <th>Channel</th>
                      <th>Report</th> 
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                      $qry="SELECT * FROM tbl_reports WHERE tbl_reports.`type`='channel' ORDER BY tbl_reports.`id` DESC LIMIT $start, $limit";   
                      $result=mysqli_query($mysqli,$qry);
                      $i=0;
                      while($row=mysqli_fetch_array($result))
                      {
                    ?>
                    <tr>
                      <td><?php echo get_user_info($row['user_id'])['name'];?></td>
                      <td><?php echo get_user_info($row['user_id'])['email'];?></td>
                      <td class="cat_action_list"><?php echo get_post($row['post_id'],$row['type'])['channel_title'];?></td>
                      <td><?php echo $row['report'];?></td>                  
                      <td>
                        <a href="manage_reports.php?report_id=<?php echo $row['id'];?>" onclick="return confirm('Are you sure you want to delete this channel report?');" class="btn btn-danger btn_delete"><i class="fa fa-trash"></i> Delete</a>
                      </td>
                    </tr>
                    <?php
                      $i++;
                      }
                    ?>
                  </tbody>
                </table>
              </div>
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
  $('a[data-toggle="tab"]').on('show.bs.tab', function(e) {
    localStorage.setItem('activeTab', $(e.target).attr('href'));
  });
  var activeTab = localStorage.getItem('activeTab');
  if(activeTab){
    $('.nav-tabs a[href="' + activeTab + '"]').tab('show');
  }
</script>