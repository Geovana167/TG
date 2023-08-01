<?php include('includes/header.php'); 

    include('includes/function.php');
	include('language/language.php');  

	
	function get_post($post_id,$type)
	{
		global $mysqli;
		switch ($type) {
		  case 'series':
		    $sql="SELECT * FROM tbl_series where id='$post_id' AND status='1'";
		    $res=mysqli_query($mysqli,$sql);
			$data=mysqli_fetch_assoc($res);

			return $data['series_name'];
		    break;

		  case 'movie':
		    $sql="SELECT * FROM tbl_movies where id='$post_id' AND status='1'";
		    $res=mysqli_query($mysqli,$sql);
			$data=mysqli_fetch_assoc($res);

			return $data['movie_title'];
		    break;

		  case 'channel':
		    $sql="SELECT * FROM tbl_channels where id='$post_id' AND status='1'";
		    $res=mysqli_query($mysqli,$sql);
			$data=mysqli_fetch_assoc($res);

			return $data['channel_title'];
		    break;
		  
		  default:
	    	break;
	}


	}
	
	if(isset($_GET['f_comment'])){

		$type=$_GET['f_comment'];

		$tableName="tbl_comments";		
		$targetpage = "manage_comments.php?f_comment=".$type; 	
		$limit = 12; 

		$query = "SELECT COUNT(*) as num FROM $tableName WHERE `type`='$type'";
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

		$users_qry="SELECT tbl_comments.*, tbl_users.`name` FROM tbl_comments, tbl_users 
					WHERE tbl_comments.`user_id`=tbl_users.`id`
					AND tbl_comments.`type`='$type'
				  	ORDER BY tbl_comments.`id` DESC LIMIT $start, $limit";  
	}
	else{
		$tableName="tbl_comments";		
		$targetpage = "manage_comments.php"; 	
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


		$users_qry="SELECT tbl_comments.*, tbl_users.`name` FROM tbl_comments, tbl_users 
					WHERE tbl_comments.`user_id`=tbl_users.`id`
				  	ORDER BY tbl_comments.`id` DESC LIMIT $start, $limit";  
	}

	$users_result=mysqli_query($mysqli,$users_qry) or die(mysqli_error($mysqli));
							
	function nicetime($date)
	{
	    if(empty($date)) {
	        return "No date provided";
	    }
	    
	    $periods         = array("second", "minute", "hour", "day", "week", "month", "year", "decade");
	    $lengths         = array("60","60","24","7","4.35","12","10");
	    
	    $now             = time();
	    $unix_date       = $date;
	    
	    // check validity of date
	    if(empty($unix_date)) {    
	        return "Bad date";
	    }

	    // is it future date or past date
	    if($now > $unix_date) {    
	        $difference     = $now - $unix_date;
	        $tense         = "ago";
	        
	    } else {
	        $difference     = $unix_date - $now;
	        $tense         = "from now";
	    }
	    
	    for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
	        $difference /= $lengths[$j];
	    }
	    
	    $difference = round($difference);
	    
	    if($difference != 1) {
	        $periods[$j].= "s";
	    }
	    
	    return "$difference $periods[$j] {$tense}";
	}
	
	
?>


 <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="page_title_block">
            <div class="col-md-5 col-xs-12">
              <div class="page_title">Manage Comments</div>
            </div>	
	          <div class="col-md-12">
	            <h4 style="float: left;">Filter: |</h4>
	            <div class="search_list" style="padding: 0px 0px 5px;float: left;margin-left: 10px">
	              	<select name="f_comment" class="form-control f_comment" style="padding: 5px 10px;height: 40px;">
	                  <option value="">--Comments of--</option>
	                  <option value="movie" <?php if(isset($_GET['f_comment']) && $_GET['f_comment']=='movie'){ echo 'selected';} ?>>Movies</option>
	                  <option value="series" <?php if(isset($_GET['f_comment']) && $_GET['f_comment']=='series'){ echo 'selected';} ?>>TV Series</option>
	                  <option value="channel" <?php if(isset($_GET['f_comment']) && $_GET['f_comment']=='channel'){ echo 'selected';} ?>>Channels</option>
	                </select>
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
          <div class="card-body mrg_bottom">
            <?php
				$i=0;
				while($users_row=mysqli_fetch_array($users_result))
				{
					 
			?>
            <div class="col-md-6">
			  <ul class="timeline timeline-simple">
				<li class="timeline-inverted">
				  <div class="timeline-badge danger"> 
					<img src="assets/images/photo.jpg" class="img-profile"> 
				  </div>
				  <div class="timeline-panel">
					<div class="timeline-heading"> 
						<a href="javascript:void(0)" title="">
							<span class="label label-danger">
								<?php echo get_post($users_row['post_id'],$users_row['type']);?>
							</span> 
						</a> 
						<span class="pull-right text-center"> 
						<a href="" class="btn_delete_a" data-id="<?php echo $users_row['id'];?>" data-toggle="tooltip" data-tooltip="Delete"> <i class="fa fa-trash" style="color:red"></i> </a> 
						 </span> 
					</div>
					<div class="timeline-body">
					  <p><?php echo $users_row['comment_text'];?></p>
					</div>
					<hr>
					<a href="#" title=""> <small class="label label-rose"> <span><?php echo $users_row['name'];?></span> </small> </a> <span class="pull-right about_time" title="about 1 hour ago"><?php echo nicetime($users_row['comment_on']);?></span>
				</div>
				</li>
			  </ul>
			</div>
				<?php		
				$i++;
				}
			?>
			 
			 
			 
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
		<div class="clearfix"></div>
      </div>
    </div>   



<?php include('includes/footer.php');?>          

<script type="text/javascript">
	$(".btn_delete_a").on("click",function(e){

		var _id=$(this).data("id");

	    if(confirm("Are you sure you want to delete this comment?")){
          $.ajax({
            type:'post',
            url:'processData.php',
            dataType:'json',
            data:{id:_id,'action':'removeComment'},
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
	});


	$(".f_comment").change(function(e){
		if($(this).val()!=''){
			window.location.href='manage_comments.php?f_comment='+$(this).val();
		}
		else{
			window.location.href='manage_comments.php';
		}
	});

</script>
