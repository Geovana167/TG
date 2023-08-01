<?php 
	include("includes/connection.php");
 	include("includes/function.php"); 
 	include("smtp_email.php");

 	define("API_LANG_ORDER_BY",$settings_details['api_lan_order_by']);
    define("API_GENR_ORDER_BY",$settings_details['api_gen_order_by']);

    define("API_PAGE_LIMIT",$settings_details['api_page_limit']);
   
	error_reporting(0);

	date_default_timezone_set("Asia/Kolkata");
	
	$protocol = strtolower( substr( $_SERVER[ 'SERVER_PROTOCOL' ], 0, 5 ) ) == 'https' ? 'https' : 'http'; 

	$file_path = $protocol.'://'.$_SERVER['SERVER_NAME'] . dirname($_SERVER['REQUEST_URI']).'/';


	if($settings_details['envato_buyer_name']=='' OR $settings_details['envato_purchase_code']=='' OR $settings_details['envato_purchased_status']==0) {  

		$set['LIVETV'] =array('msg' => 'Purchase code verification failed!' );	
		
		header( 'Content-Type: application/json; charset=utf-8' );
	    echo $val= str_replace('\\/', '/', json_encode($set,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
		die();
	}

	function get_user_info($user_id)
	{
		global $mysqli;

		$user_qry="SELECT * FROM tbl_users where id='".$user_id."'";
		$user_result=mysqli_query($mysqli,$user_qry);
		$user_row=mysqli_fetch_assoc($user_result);

		return $user_row['name'];
	}

	function get_user_status($user_id)
	{
		global $mysqli;

		$user_qry="SELECT * FROM tbl_users where id='".$user_id."'";
		$user_result=mysqli_query($mysqli,$user_qry);
		$user_row=mysqli_fetch_assoc($user_result);

		if(mysqli_num_rows($user_result) > 0){
			if($user_row['status']==0){
				return 'false';
			}
			else if($user_row['status']==1){
				return 'true';
			}
		}
		else{
			return 'false';
		}

		
	}
  
  	$get_method = checkSignSalt($_POST['data']);	 

 	if($get_method['method_name']=="get_home")
  	{

  		$search_text=trim($get_method['search_text']);

  		//get slider

  		$jsonObj= array();
  		
  		$sql="SELECT movie.*, lang.`language_name`, lang.`language_background` FROM tbl_movies movie
				LEFT JOIN tbl_language lang ON movie.`language_id`=lang.`id`
				WHERE movie.`status`='1' AND lang.`status`='1' AND movie.`is_slider`='1' ORDER BY movie.`id` DESC";

		$res=mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));

		while ($data_row=mysqli_fetch_assoc($res)) {
			$data['id']=$data_row['id'];
			$data['title']=$data_row['movie_title'];
			$data['sub_title']=$data_row['language_name'];
			$data['slide_image']=$file_path.'images/movies/'.$data_row['movie_cover'];
			$data['type']='movie';

			array_push($jsonObj,$data);
		}

		mysqli_free_result($res);
		
		$data=array();

		$sql="SELECT * FROM tbl_series WHERE `status`='1' AND is_slider='1' ORDER BY `id` DESC";

		$res=mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));

		while ($data_row=mysqli_fetch_assoc($res)) {
			$data['id']=$data_row['id'];
			$data['title']=$data_row['series_name'];
			$data['sub_title']='';
			$data['slide_image']=$file_path.'images/series/'.$data_row['series_cover'];
			$data['type']='series';

			array_push($jsonObj,$data);
		}

		mysqli_free_result($res);

		$data=array();

		$sql="SELECT * FROM tbl_channels
				LEFT JOIN tbl_category ON tbl_channels.`cat_id`= tbl_category.`cid` 
				WHERE tbl_channels.`status`='1' AND tbl_channels.`slider_channel`='1' ORDER BY tbl_channels.`id` DESC";

		$res=mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));

		while ($data_row=mysqli_fetch_assoc($res)) {

			$data['id'] = $data_row['id'];
			$data['title'] = $data_row['channel_title'];
			$data['sub_title'] = $data_row['category_name'];
			$data['slide_image'] = $file_path.'images/'.$data_row['channel_thumbnail'];
			$data['type'] = 'channel';

			array_push($jsonObj,$data);
		}

		$row['banner']=$jsonObj;

		// get category list
 		$jsonObj= array();
 		$data=array();

		$sql="SELECT cid,category_name,category_image FROM tbl_category WHERE `status`='1' ORDER BY tbl_category.`cid` DESC LIMIT 0,5";
		$res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli));

		while($data_row = mysqli_fetch_assoc($res))
		{
			
			$data['cid'] = $data_row['cid'];
			$data['category_name'] = $data_row['category_name'];
			$data['category_image'] = $file_path.'images/'.$data_row['category_image'];
			$data['category_image_thumb'] = $file_path.'images/thumbs/'.$data_row['category_image'];
			 
			array_push($jsonObj,$data);
		}

		mysqli_free_result($res);
		$row['cat_list']=$jsonObj;

		$jsonObj = array();
		$data=array();

		$sql="SELECT movie.*, lang.`language_name`, lang.`language_background` FROM tbl_movies movie
				LEFT JOIN tbl_language lang ON movie.`language_id`=lang.`id`
				LEFT JOIN tbl_genres genres ON movie.`genre_id`=genres.`gid`
				WHERE movie.`status`='1' AND lang.`status`='1' ORDER BY movie.`id` DESC LIMIT 0,5";


		$res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli));

		while($data_row = mysqli_fetch_assoc($res))
		{
			$data['id'] = $data_row['id'];
			$data['language_id'] = $data_row['language_id'];
			$data['genre_id'] = $data_row['genre_id'];
			$data['movie_title'] = $data_row['movie_title'];
			$data['movie_desc'] = addslashes($data_row['movie_desc']);

			$data['movie_poster'] = $file_path.'images/movies/'.$data_row['movie_poster'];
			$data['movie_poster_thumb'] = $file_path.'images/movies/thumbs/'.$data_row['movie_poster'];

			$data['movie_cover'] = $file_path.'images/movies/'.$data_row['movie_cover'];
			$data['movie_cover_thumb'] = $file_path.'images/movies/thumbs/'.$data_row['movie_cover'];

			if($data_row['movie_type']=='local'){
				$data['video_type'] = 'local_url';
			}else{
				$data['video_type'] = $data_row['movie_type'];	
			}

			if($data_row['movie_type']=='local'){
				$data['movie_url'] = $file_path.'uploads/'.$data_row['movie_url'];				
			}else{
				$data['movie_url'] = $data_row['movie_url'];				
			}

			$data['total_views'] = $data_row['total_views'];
			$data['total_rate'] = $data_row['total_rate'];
			$data['rate_avg'] = $data_row['rate_avg'];

			$data['language_name'] = $data_row['language_name'];
			$data['language_background'] = '#'.$data_row['language_background'];
			 

			array_push($jsonObj,$data);
		
		}

		mysqli_free_result($res);
		$row['latest_movies']=$jsonObj;

		// tv series ..
		$jsonObj = array();
		$data=array();

		$sql="SELECT * FROM tbl_series WHERE `status`='1' ORDER BY `id` DESC LIMIT 0,5";
		$res = mysqli_query($mysqli,$sql)or die(mysqli_error($mysqli));

		while($data_row = mysqli_fetch_assoc($res))
		{
			
			$data['id'] = $data_row['id'];
			$data['series_name'] = $data_row['series_name'];
			$data['series_desc'] = addslashes($data_row['series_desc']);
			$data['series_poster'] = $file_path.'images/series/'.$data_row['series_poster'];
			$data['series_poster_thumb'] = $file_path.'images/series/thumbs/'.$data_row['series_poster'];
			$data['series_cover'] = $file_path.'images/series/'.$data_row['series_cover'];
			$data['series_cover_thumb'] = $file_path.'images/series/thumbs/'.$data_row['series_cover'];

			array_push($jsonObj,$data);
		
		}

		mysqli_free_result($res);
		$row['tv_series']=$jsonObj;

		// latest channels ..
		$jsonObj = array();
		$data=array();

		$sql="SELECT * FROM tbl_channels
			LEFT JOIN tbl_category ON tbl_channels.`cat_id`= tbl_category.`cid` 
			WHERE tbl_channels.`status`='1' ORDER BY tbl_channels.`id` DESC LIMIT 0,5";

		$res = mysqli_query($mysqli,$sql)or die(mysqli_error($mysqli));

		while($data_row = mysqli_fetch_assoc($res))
		{
			$data['id'] = $data_row['id'];
			$data['cat_id'] = $data_row['cat_id'];
			$data['channel_title'] = $data_row['channel_title'];
			$data['channel_url'] = $data_row['channel_url'];
			$data['channel_url_ios'] = $data_row['channel_url_ios'];
			$data['channel_thumbnail'] = $file_path.'images/'.$data_row['channel_thumbnail'];
			$data['channel_desc'] = $data_row['channel_desc'];

			$data['total_views'] = $data_row['total_views'];
			$data['total_rate'] = $data_row['total_rate'];
			$data['rate_avg'] = $data_row['rate_avg'];

			$data['cid'] = $data_row['cid'];
			$data['category_name'] = $data_row['category_name'];
			$data['category_image'] = $file_path.'images/'.$data_row['category_image'];
			$data['category_image_thumb'] = $file_path.'images/thumbs/'.$data_row['category_image'];
			 

			array_push($jsonObj,$data);
		
		}

		mysqli_free_result($res);
		$row['latest_channels']=$jsonObj;

		
		$jsonObj = array();
		$data=array();

		if($search_text!=''){

			// search series ..
			$sql="SELECT * FROM tbl_series WHERE `status`='1' AND (`series_name` LIKE '%$search_text%' OR `series_desc` LIKE '%$search_text%') ORDER BY `id` DESC LIMIT 0,5";

			$result=mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli));

			if(mysqli_num_rows($result) > 0){
				
				while($data_row = mysqli_fetch_assoc($result))
				{
					$data['id'] = $data_row['id'];
					$data['series_name'] = $data_row['series_name'];
					$data['series_desc'] = addslashes($data_row['series_desc']);
					$data['series_poster'] = $file_path.'images/series/'.$data_row['series_poster'];
					$data['series_poster_thumb'] = $file_path.'images/series/thumbs/'.$data_row['series_poster'];
					$data['series_cover'] = $file_path.'images/series/'.$data_row['series_cover'];
					$data['series_cover_thumb'] = $file_path.'images/series/thumbs/'.$data_row['series_cover'];

					$data['total_views'] = $data_row['total_views'];
					$data['total_rate'] = $data_row['total_rate'];
					$data['rate_avg'] = $data_row['rate_avg'];

					array_push($jsonObj,$data);

				}
				$row['search_series']=$jsonObj;	
					 
			}
			else
			{
				$row['search_series']=$jsonObj;	

			}
			mysqli_free_result($res);

			$jsonObj = array();
			$data=array();

			// search movies

			$sql="SELECT movie.*, lang.`language_name`, lang.`language_background` FROM tbl_movies movie
				LEFT JOIN tbl_language lang ON movie.`language_id`=lang.`id`
				LEFT JOIN tbl_genres genres ON movie.`genre_id`=genres.`gid`
				WHERE movie.`status`='1' AND lang.`status`='1' AND (lang.`language_name` LIKE '%$search_text%' OR genres.`genre_name` LIKE '%$search_text%' OR movie.`movie_title` LIKE '%$search_text%' OR movie.`movie_desc` LIKE '%$search_text%') ORDER BY movie.`id` DESC LIMIT 0, 5";

			$result=mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli));

			if(mysqli_num_rows($result) > 0){
				while($data_row = mysqli_fetch_assoc($result))
				{
					$data['id'] = $data_row['id'];
					$data['language_id'] = $data_row['language_id'];
					$data['genre_id'] = $data_row['genre_id'];
					$data['movie_title'] = $data_row['movie_title'];
					$data['movie_desc'] = addslashes($data_row['movie_desc']);

					$data['movie_poster'] = $file_path.'images/movies/'.$data_row['movie_poster'];
					$data['movie_poster_thumb'] = $file_path.'images/movies/thumbs/'.$data_row['movie_poster'];

					$data['movie_cover'] = $file_path.'images/movies/'.$data_row['movie_cover'];
					$data['movie_cover_thumb'] = $file_path.'images/movies/thumbs/'.$data_row['movie_cover'];

					if($data_row['movie_type']=='local'){
						$data['video_type'] = 'local_url';
					}else{
						$data['video_type'] = $data_row['movie_type'];	
					}
					

					if($data_row['movie_type']=='local'){
						$data['movie_url'] = $file_path.'uploads/'.$data_row['movie_url'];				
					}else{
						$data['movie_url'] = $data_row['movie_url'];				
					}

					$data['total_views'] = $data_row['total_views'];
					$data['total_rate'] = $data_row['total_rate'];
					$data['rate_avg'] = $data_row['rate_avg'];

					$data['language_name'] = $data_row['language_name'];
					$data['language_background'] = '#'.$data_row['language_background'];

					array_push($jsonObj,$data);

				}
				$row['search_movies']=$jsonObj;
					 
			}
			else
			{
				$row['search_movies']=$jsonObj;

			}
			mysqli_free_result($res);

			$jsonObj = array();
			$data=array();

			// search channels

			$query="SELECT * FROM tbl_channels
				LEFT JOIN tbl_category ON tbl_channels.`cat_id`= tbl_category.`cid` 
				WHERE tbl_channels.`status`='1' AND (tbl_channels.`channel_title` LIKE '%$search_text%' OR tbl_channels.`channel_desc` LIKE '%$search_text%') LIMIT 0, 5";

			$sql = mysqli_query($mysqli,$query);
			
			if(mysqli_num_rows($sql) > 0){

					while($data_row = mysqli_fetch_assoc($sql))
					{
						$data['id'] = $data_row['id'];
						$data['cat_id'] = $data_row['cat_id'];
						$data['channel_title'] = $data_row['channel_title'];
						$data['channel_url'] = $data_row['channel_url'];
						$data['channel_url_ios'] = $data_row['channel_url_ios'];
						$data['channel_thumbnail'] = $file_path.'images/'.$data_row['channel_thumbnail'];
						$data['channel_desc'] = $data_row['channel_desc'];

						$data['rate_avg'] = $data_row['rate_avg'];

						$data['cid'] = $data_row['cid'];
						$data['category_name'] = $data_row['category_name'];
						$data['category_image'] = $file_path.'images/'.$data_row['category_image'];
						$data['category_image_thumb'] = $file_path.'images/thumbs/'.$data_row['category_image'];
						 

						array_push($jsonObj,$data);
					
					}

					$row['search_channels']=$jsonObj;
					 
			}
			else
			{
				$row['search_channels']=$jsonObj;
			}
			mysqli_free_result($res);


			
		}
		else{
			$row['search_series']=$jsonObj;		
			$row['search_movies']=$jsonObj;		
			$row['search_channels']=$jsonObj;		
		}


		$set['LIVETV'] = $row;

		header( 'Content-Type: application/json; charset=utf-8' );
	    echo $val= str_replace('\\/', '/', json_encode($set,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
		die();

  	}
  	if($get_method['method_name']=="search_all")
  	{

  		$search_text=trim($get_method['search_text']);

		$jsonObj = array();
		$data=array();


		// search series ..
		$sql="SELECT * FROM tbl_series WHERE `status`='1' AND (`series_name` LIKE '%$search_text%' OR `series_desc` LIKE '%$search_text%') ORDER BY `id` DESC LIMIT 0,5";

		$result=mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli));

		if(mysqli_num_rows($result) > 0){
			
			while($data_row = mysqli_fetch_assoc($result))
			{
				$data['id'] = $data_row['id'];
				$data['series_name'] = $data_row['series_name'];
				$data['series_desc'] = addslashes($data_row['series_desc']);
				$data['series_poster'] = $file_path.'images/series/'.$data_row['series_poster'];
				$data['series_poster_thumb'] = $file_path.'images/series/thumbs/'.$data_row['series_poster'];
				$data['series_cover'] = $file_path.'images/series/'.$data_row['series_cover'];
				$data['series_cover_thumb'] = $file_path.'images/series/thumbs/'.$data_row['series_cover'];

				$data['total_views'] = $data_row['total_views'];
				$data['total_rate'] = $data_row['total_rate'];
				$data['rate_avg'] = $data_row['rate_avg'];

				array_push($jsonObj,$data);

			}
			$row['search_series']=$jsonObj;	
				 
		}
		else
		{
			$row['search_series']=$jsonObj;	

		}
		mysqli_free_result($res);

		$jsonObj = array();
		$data=array();

		// search movies

		$sql="SELECT movie.*, lang.`language_name`, lang.`language_background` FROM tbl_movies movie
			LEFT JOIN tbl_language lang ON movie.`language_id`=lang.`id`
			LEFT JOIN tbl_genres genres ON movie.`genre_id`=genres.`gid`
			WHERE movie.`status`='1' AND lang.`status`='1' AND (lang.`language_name` LIKE '%$search_text%' OR genres.`genre_name` LIKE '%$search_text%' OR movie.`movie_title` LIKE '%$search_text%' OR movie.`movie_desc` LIKE '%$search_text%') ORDER BY movie.`id` DESC LIMIT 0, 5";

		$result=mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli));

		if(mysqli_num_rows($result) > 0){
			while($data_row = mysqli_fetch_assoc($result))
			{
				$data['id'] = $data_row['id'];
				$data['language_id'] = $data_row['language_id'];
				$data['genre_id'] = $data_row['genre_id'];
				$data['movie_title'] = $data_row['movie_title'];
				$data['movie_desc'] = addslashes($data_row['movie_desc']);

				$data['movie_poster'] = $file_path.'images/movies/'.$data_row['movie_poster'];
				$data['movie_poster_thumb'] = $file_path.'images/movies/thumbs/'.$data_row['movie_poster'];

				$data['movie_cover'] = $file_path.'images/movies/'.$data_row['movie_cover'];
				$data['movie_cover_thumb'] = $file_path.'images/movies/thumbs/'.$data_row['movie_cover'];

				if($data_row['movie_type']=='local'){
					$data['video_type'] = 'local_url';
				}else{
					$data['video_type'] = $data_row['movie_type'];	
				}
				

				if($data_row['movie_type']=='local'){
					$data['movie_url'] = $file_path.'uploads/'.$data_row['movie_url'];				
				}else{
					$data['movie_url'] = $data_row['movie_url'];				
				}

				$data['total_views'] = $data_row['total_views'];
				$data['total_rate'] = $data_row['total_rate'];
				$data['rate_avg'] = $data_row['rate_avg'];

				$data['language_name'] = $data_row['language_name'];
				$data['language_background'] = '#'.$data_row['language_background'];

				array_push($jsonObj,$data);

			}
			$row['search_movies']=$jsonObj;
				 
		}
		else
		{
			$row['search_movies']=$jsonObj;

		}
		mysqli_free_result($res);

		$jsonObj = array();
		$data=array();

		// search channels

		$query="SELECT * FROM tbl_channels
			LEFT JOIN tbl_category ON tbl_channels.`cat_id`= tbl_category.`cid` 
			WHERE tbl_channels.`status`='1' AND (tbl_channels.`channel_title` LIKE '%$search_text%' OR tbl_channels.`channel_desc` LIKE '%$search_text%') LIMIT 0, 5";

		$sql = mysqli_query($mysqli,$query);
		
		if(mysqli_num_rows($sql) > 0){

				while($data_row = mysqli_fetch_assoc($sql))
				{
					$data['id'] = $data_row['id'];
					$data['cat_id'] = $data_row['cat_id'];
					$data['channel_title'] = $data_row['channel_title'];
					$data['channel_url'] = $data_row['channel_url'];
					$data['channel_url_ios'] = $data_row['channel_url_ios'];
					$data['channel_thumbnail'] = $file_path.'images/'.$data_row['channel_thumbnail'];
					$data['channel_desc'] = $data_row['channel_desc'];

					$data['rate_avg'] = $data_row['rate_avg'];

					$data['cid'] = $data_row['cid'];
					$data['category_name'] = $data_row['category_name'];
					$data['category_image'] = $file_path.'images/'.$data_row['category_image'];
					$data['category_image_thumb'] = $file_path.'images/thumbs/'.$data_row['category_image'];
					 

					array_push($jsonObj,$data);
				
				}

				$row['search_channels']=$jsonObj;
				 
		}
		else
		{
			$row['search_channels']=$jsonObj;
		}
		mysqli_free_result($res);



		$set['LIVETV'] = $row;

		header( 'Content-Type: application/json; charset=utf-8' );
	    echo $val= str_replace('\\/', '/', json_encode($set,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
		die();

  	}
  	else if($get_method['method_name']=="get_latest_channels")
  	{
  		$latest_limit=API_LATEST_LIMIT;
  		$jsonObj= array();

  		$page_limit=API_PAGE_LIMIT;

  		$total_pages=round($latest_limit/$page_limit);
			
		$limit=($get_method['page']-1) * $page_limit;

		$actual_limit=$get_method['page']*$page_limit;

		if($actual_limit <= $latest_limit){
			$page_limit=API_PAGE_LIMIT;
		}
		else if($get_method['page'] <= $total_pages){
			$page_limit=$latest_limit-$page_limit;
		}
		else{
			$page_limit=0;	
		}

		$query="SELECT * FROM tbl_channels
			LEFT JOIN tbl_category ON tbl_channels.`cat_id`= tbl_category.`cid` 
			WHERE tbl_channels.`status`='1' ORDER BY tbl_channels.`id` DESC LIMIT $limit, $page_limit";
			
		$sql = mysqli_query($mysqli,$query)or die(mysqli_error($mysqli));

		while($data = mysqli_fetch_assoc($sql))
		{
			$row['id'] = $data['id'];
			$row['cat_id'] = $data['cat_id'];
			$row['channel_title'] = $data['channel_title'];
			$row['channel_url'] = $data['channel_url'];
			$row['channel_url_ios'] = $data['channel_url_ios'];
			$row['channel_thumbnail'] = $file_path.'images/'.$data['channel_thumbnail'];
			$row['channel_desc'] = $data['channel_desc'];

			$row['total_views'] = $data['total_views'];
			$row['total_rate'] = $data['total_rate'];
			$row['rate_avg'] = $data['rate_avg'];

			$row['cid'] = $data['cid'];
			$row['category_name'] = $data['category_name'];
			$row['category_image'] = $file_path.'images/'.$data['category_image'];
			$row['category_image_thumb'] = $file_path.'images/thumbs/'.$data['category_image'];
			 

			array_push($jsonObj,$row);
		
		}

		$set['LIVETV'] = $jsonObj;

  		header( 'Content-Type: application/json; charset=utf-8' );
	    echo $val= str_replace('\\/', '/', json_encode($set,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
		die();
  	}
  	else if($get_method['method_name']=="get_latest_movies")
  	{
  		$latest_limit=API_LATEST_LIMIT;
  		$jsonObj= array();

  		$page_limit=API_PAGE_LIMIT;

  		$total_pages=round($latest_limit/$page_limit);
			
		$limit=($get_method['page']-1) * $page_limit;

		$actual_limit=$get_method['page']*$page_limit;

		if($actual_limit <= $latest_limit){
			$page_limit=API_PAGE_LIMIT;
		}
		else if($get_method['page'] <= $total_pages){
			$page_limit=$latest_limit-$page_limit;
		}
		else{
			$page_limit=0;	
		}

		$query="SELECT movie.*, lang.`language_name`, lang.`language_background` FROM tbl_movies movie
				LEFT JOIN tbl_language lang ON movie.`language_id`=lang.`id`
				LEFT JOIN tbl_genres genres ON movie.`genre_id`=genres.`gid`
				WHERE movie.`status`='1' AND lang.`status`='1' ORDER BY movie.`id` DESC LIMIT $limit, $page_limit";


		$sql = mysqli_query($mysqli,$query)or die(mysqli_error($mysqli));

		while($data = mysqli_fetch_assoc($sql))
		{
			$row['id'] = $data['id'];
			$row['language_id'] = $data['language_id'];
			$row['genre_id'] = $data['genre_id'];
			$row['movie_title'] = $data['movie_title'];
			$row['movie_desc'] = addslashes($data['movie_desc']);

			$row['movie_poster'] = $file_path.'images/movies/'.$data['movie_poster'];
			$row['movie_poster_thumb'] = $file_path.'images/movies/thumbs/'.$data['movie_poster'];

			$row['movie_cover'] = $file_path.'images/movies/'.$data['movie_cover'];
			$row['movie_cover_thumb'] = $file_path.'images/movies/thumbs/'.$data['movie_cover'];

			if($data['movie_type']=='local'){
				$row['video_type'] = 'local_url';
			}else{
				$row['video_type'] = $data['movie_type'];	
			}

			if($data['movie_type']=='local'){
				$row['movie_url'] = $file_path.'uploads/'.$data['movie_url'];				
			}else{
				$row['movie_url'] = $data['movie_url'];				
			}

			$row['total_views'] = $data['total_views'];
			$row['total_rate'] = $data['total_rate'];
			$row['rate_avg'] = $data['rate_avg'];

			$row['language_name'] = $data['language_name'];
			$row['language_background'] = '#'.$data['language_background'];
			 

			array_push($jsonObj,$row);
		
		}

		$set['LIVETV'] = $jsonObj;

  		header( 'Content-Type: application/json; charset=utf-8' );
	    echo $val= str_replace('\\/', '/', json_encode($set,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
		die();
  	}
  	else if($get_method['method_name']=="get_category")
  	{
  		$jsonObj= array();
		
		$cid=API_CAT_ORDER_BY;

		$page_limit=API_PAGE_LIMIT;
			
		$limit=($get_method['page']-1) * $page_limit;

		$query="SELECT cid,category_name,category_image FROM tbl_category WHERE `status`='1' ORDER BY tbl_category.$cid LIMIT $limit, $page_limit";
		$sql = mysqli_query($mysqli,$query)or die(mysqli_error($mysqli));

		while($data = mysqli_fetch_assoc($sql))
		{
			
			$row['cid'] = $data['cid'];
			$row['category_name'] = $data['category_name'];
			$row['category_image'] = $file_path.'images/'.$data['category_image'];
			$row['category_image_thumb'] = $file_path.'images/thumbs/'.$data['category_image'];
			 

			array_push($jsonObj,$row);
		
		}

		$set['LIVETV'] = $jsonObj;

  		header( 'Content-Type: application/json; charset=utf-8' );
	    echo $val= str_replace('\\/', '/', json_encode($set,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
		die();
  	}
  	else if($get_method['method_name']=="get_language")
  	{
  		$jsonObj= array();
		
		$lang_order=API_LANG_ORDER_BY;

		$page_limit=API_PAGE_LIMIT;
			
		$limit=($get_method['page']-1) * $page_limit;

		$query="SELECT * FROM tbl_language WHERE `status`='1' ORDER BY tbl_language.$lang_order LIMIT $limit, $page_limit";
		$sql = mysqli_query($mysqli,$query)or die(mysqli_error($mysqli));

		while($data = mysqli_fetch_assoc($sql))
		{
			
			$row['id'] = $data['id'];
			$row['language_name'] = $data['language_name'];
			$row['language_background'] = '#'.$data['language_background'];

			array_push($jsonObj,$row);
		
		}

		$set['LIVETV'] = $jsonObj;

  		header( 'Content-Type: application/json; charset=utf-8' );
	    echo $val= str_replace('\\/', '/', json_encode($set,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
		die();
  	}
  	else if($get_method['method_name']=="get_genre")
  	{
  		$jsonObj= array();
		
		$genr_order=API_GENR_ORDER_BY;

		$page_limit=API_PAGE_LIMIT;
			
		$limit=($get_method['page']-1) * $page_limit;

		$query="SELECT * FROM tbl_genres ORDER BY tbl_genres.$genr_order LIMIT $limit, $page_limit";
		$sql = mysqli_query($mysqli,$query)or die(mysqli_error($mysqli));

		while($data = mysqli_fetch_assoc($sql))
		{
			
			$row['id'] = $data['gid'];
			$row['genre_name'] = $data['genre_name'];
			$row['genre_image'] = $file_path.'images/'.$data['genre_image'];
			$row['genre_image_thumb'] = $file_path.'images/thumbs/'.$data['genre_image'];

			array_push($jsonObj,$row);
		
		}

		$set['LIVETV'] = $jsonObj;

  		header( 'Content-Type: application/json; charset=utf-8' );
	    echo $val= str_replace('\\/', '/', json_encode($set,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
		die();
  	}
  	else if($get_method['method_name']=="get_movies")
  	{
  		$jsonObj= array();

  		$page_limit=API_PAGE_LIMIT;
			
		$limit=($get_method['page']-1) * $page_limit;

		$query="SELECT movie.*, lang.`language_name`, lang.`language_background` FROM tbl_movies movie
				LEFT JOIN tbl_language lang ON movie.`language_id`=lang.`id`
				LEFT JOIN tbl_genres genres ON movie.`genre_id`=genres.`gid`
				WHERE movie.`status`='1' AND lang.`status`='1' ORDER BY movie.`id` DESC LIMIT $limit, $page_limit";

		$sql = mysqli_query($mysqli,$query)or die(mysqli_error($mysqli));

		while($data = mysqli_fetch_assoc($sql))
		{
			$row['id'] = $data['id'];
			$row['language_id'] = $data['language_id'];
			$row['genre_id'] = $data['genre_id'];
			$row['movie_title'] = $data['movie_title'];
			$row['movie_desc'] = addslashes($data['movie_desc']);

			$row['movie_poster'] = $file_path.'images/movies/'.$data['movie_poster'];
			$row['movie_poster_thumb'] = $file_path.'images/movies/thumbs/'.$data['movie_poster'];

			$row['movie_cover'] = $file_path.'images/movies/'.$data['movie_cover'];
			$row['movie_cover_thumb'] = $file_path.'images/movies/thumbs/'.$data['movie_cover'];

			if($data['movie_type']=='local'){
				$row['video_type'] = 'local_url';
			}else{
				$row['video_type'] = $data['movie_type'];	
			}

			if($data['movie_type']=='local'){
				$row['movie_url'] = $file_path.'uploads/'.$data['movie_url'];				
			}else{
				$row['movie_url'] = $data['movie_url'];				
			}

			

			$row['total_views'] = $data['total_views'];
			$row['total_rate'] = $data['total_rate'];
			$row['rate_avg'] = $data['rate_avg'];

			$row['language_name'] = $data['language_name'];
			$row['language_background'] = '#'.$data['language_background'];

			array_push($jsonObj,$row);
		
		}

		$set['LIVETV'] = $jsonObj;

  		header( 'Content-Type: application/json; charset=utf-8' );
	    echo $val= str_replace('\\/', '/', json_encode($set,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
		die();
  	}

  	else if($get_method['method_name']=="get_series")
  	{
  		$jsonObj= array();

  		$page_limit=API_PAGE_LIMIT;
			
		$limit=($get_method['page']-1) * $page_limit;

		$query="SELECT * FROM tbl_series WHERE `status`='1' ORDER BY `id` DESC LIMIT $limit, $page_limit";
		$sql = mysqli_query($mysqli,$query)or die(mysqli_error($mysqli));

		while($data = mysqli_fetch_assoc($sql))
		{
			
			$row['id'] = $data['id'];
			$row['series_name'] = $data['series_name'];
			$row['series_desc'] = addslashes($data['series_desc']);
			$row['series_poster'] = $file_path.'images/series/'.$data['series_poster'];
			$row['series_poster_thumb'] = $file_path.'images/series/thumbs/'.$data['series_poster'];
			$row['series_cover'] = $file_path.'images/series/'.$data['series_cover'];
			$row['series_cover_thumb'] = $file_path.'images/series/thumbs/'.$data['series_cover'];

			array_push($jsonObj,$row);
		
		}

		$set['LIVETV'] = $jsonObj;

  		header( 'Content-Type: application/json; charset=utf-8' );
	    echo $val= str_replace('\\/', '/', json_encode($set,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
		die();
  	}
  	else if($get_method['method_name']=="get_episodes")
  	{
  		$series_id=$get_method['series_id'];
  		$season_id=$get_method['season_id'];

  		$jsonObj= array();

		$query="SELECT * FROM tbl_episode WHERE `status`='1' AND `series_id`='$series_id' AND `season_id`='$season_id' ORDER BY `id` ASC";
		$sql = mysqli_query($mysqli,$query) or die(mysqli_error($mysqli));

		while($data = mysqli_fetch_assoc($sql))
		{
			
			$row['id'] = $data['id'];
			$row['episode_title'] = $data['episode_title'];

			if($data['episode_type']=='local'){
				$row['episode_type'] = 'local_url';
			}else{
				$row['episode_type'] = $data['episode_type'];	
			}

			if($data['episode_type']=='local'){
				$row['episode_url'] = $file_path.'uploads/'.$data['episode_url'];				
			}else{
				$row['episode_url'] = $data['episode_url'];				
			}

			$row['video_id'] = $data['video_id'];

			$row['episode_poster'] = $file_path.'images/episodes/'.$data['episode_poster'];
			$row['episode_poster_thumb'] = $file_path.'images/episodes/thumbs/'.$data['episode_poster'];

			array_push($jsonObj,$row);
		
		}

		$set['LIVETV'] = $jsonObj;

  		header( 'Content-Type: application/json; charset=utf-8' );
	    echo $val= str_replace('\\/', '/', json_encode($set,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
		die();
  	}
  	else if($get_method['method_name']=="get_movies_by_lang_id")
  	{

  		$lang_id=$get_method['lang_id'];

  		$page_limit=API_PAGE_LIMIT;
			
		$limit=($get_method['page']-1) * $page_limit;

  		$jsonObj= array();

		$query="SELECT movie.*, lang.`language_name`, lang.`language_background` FROM tbl_movies movie
				LEFT JOIN tbl_language lang ON movie.`language_id`=lang.`id`
				WHERE movie.`status`='1' AND lang.`status`='1' AND movie.`language_id`='$lang_id' ORDER BY movie.`id` DESC LIMIT $limit, $page_limit";


		$sql = mysqli_query($mysqli,$query) or die(mysqli_error($mysqli));

		while($data = mysqli_fetch_assoc($sql))
		{
			$row['id'] = $data['id'];
			$row['language_id'] = $data['language_id'];
			$row['genre_id'] = $data['genre_id'];
			$row['movie_title'] = $data['movie_title'];
			$row['movie_desc'] = addslashes($data['movie_desc']);

			$row['movie_poster'] = $file_path.'images/movies/'.$data['movie_poster'];
			$row['movie_poster_thumb'] = $file_path.'images/movies/thumbs/'.$data['movie_poster'];

			$row['movie_cover'] = $file_path.'images/movies/'.$data['movie_cover'];
			$row['movie_cover_thumb'] = $file_path.'images/movies/thumbs/'.$data['movie_cover'];

			if($data['movie_type']=='local'){
				$row['video_type'] = 'local_url';
			}else{
				$row['video_type'] = $data['movie_type'];	
			}

			if($data['movie_type']=='local'){
				$row['movie_url'] = $file_path.'uploads/'.$data['movie_url'];				
			}else{
				$row['movie_url'] = $data['movie_url'];				
			}

			$row['total_views'] = $data['total_views'];
			$row['total_rate'] = $data['total_rate'];
			$row['rate_avg'] = $data['rate_avg'];

			$row['language_name'] = $data['language_name'];
			$row['language_background'] = '#'.$data['language_background'];
			 

			array_push($jsonObj,$row);
		
		}

		$set['LIVETV'] = $jsonObj;

  		header( 'Content-Type: application/json; charset=utf-8' );
	    echo $val= str_replace('\\/', '/', json_encode($set,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
		die();
  	}
  	
  	else if($get_method['method_name']=="get_movies_by_gen_id")
  	{

  		$page_limit=API_PAGE_LIMIT;
			
		$limit=($get_method['page']-1) * $page_limit;

  		$genre_id=explode(',', $get_method['genre_id']);	// 2, 4

  		$jsonObj= array();

  		if($genre_id[0]!=''){
			$column='';
			foreach ($genre_id as $key => $value) {
				$column.='FIND_IN_SET('.$value.', movie.`genre_id`) OR ';
			}

			$column=rtrim($column,'OR ');

			$query="SELECT movie.*, lang.`language_name`, lang.`language_background` FROM tbl_movies movie
				LEFT JOIN tbl_language lang ON movie.`language_id`=lang.`id`
				LEFT JOIN tbl_genres genres ON movie.`genre_id`=genres.`gid`
				WHERE ($column) AND movie.`status`='1' AND lang.`status`='1' ORDER BY movie.`id` DESC LIMIT $limit, $page_limit";

		}


		$sql = mysqli_query($mysqli,$query) or die(mysqli_error($mysqli));

		while($data = mysqli_fetch_assoc($sql))
		{
			$row['id'] = $data['id'];
			$row['language_id'] = $data['language_id'];
			$row['genre_id'] = $data['genre_id'];
			$row['movie_title'] = $data['movie_title'];
			$row['movie_desc'] = addslashes($data['movie_desc']);

			$row['movie_poster'] = $file_path.'images/movies/'.$data['movie_poster'];
			$row['movie_poster_thumb'] = $file_path.'images/movies/thumbs/'.$data['movie_poster'];

			$row['movie_cover'] = $file_path.'images/movies/'.$data['movie_cover'];
			$row['movie_cover_thumb'] = $file_path.'images/movies/thumbs/'.$data['movie_cover'];

			if($data['movie_type']=='local'){
				$row['video_type'] = 'local_url';
			}else{
				$row['video_type'] = $data['movie_type'];	
			}

			if($data['movie_type']=='local'){
				$row['movie_url'] = $file_path.'uploads/'.$data['movie_url'];				
			}else{
				$row['movie_url'] = $data['movie_url'];				
			}

			$row['total_views'] = $data['total_views'];
			$row['total_rate'] = $data['total_rate'];
			$row['rate_avg'] = $data['rate_avg'];

			$row['language_name'] = $data['language_name'];
			$row['language_background'] = '#'.$data['language_background'];
			 
			array_push($jsonObj,$row);
		
		}

		$set['LIVETV'] = $jsonObj;

  		header( 'Content-Type: application/json; charset=utf-8' );
	    echo $val= str_replace('\\/', '/', json_encode($set,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
		die();
  	}
  	
  	else if($get_method['method_name']=="get_channels_by_cat_id")
  	{		
  		$post_order_by=API_CAT_POST_ORDER_BY;

		$cat_id=$get_method['cat_id'];	

  		$page_limit=API_PAGE_LIMIT;
			
		$limit=($get_method['page']-1) * $page_limit;

  		$jsonObj= array();
	
	    $query="SELECT * FROM tbl_channels
			LEFT JOIN tbl_category ON tbl_channels.`cat_id`= tbl_category.`cid` 
			WHERE tbl_channels.`cat_id`='$cat_id' AND tbl_channels.`status`='1' ORDER BY tbl_channels.$post_order_by LIMIT $limit, $page_limit";

		$sql = mysqli_query($mysqli,$query)or die(mysqli_error($mysqli));

		while($data = mysqli_fetch_assoc($sql))
		{
			$row['id'] = $data['id'];
			$row['cat_id'] = $data['cat_id'];
			$row['channel_title'] = $data['channel_title'];
			$row['channel_url'] = $data['channel_url'];
			$row['channel_url_ios'] = $data['channel_url_ios'];
			$row['channel_thumbnail'] = $file_path.'images/'.$data['channel_thumbnail'];
			$row['channel_desc'] = $data['channel_desc'];

			$row['total_views'] = $data['total_views'];
			$row['total_rate'] = $data['total_rate'];
			$row['rate_avg'] = $data['rate_avg'];

			$row['cid'] = $data['cid'];
			$row['category_name'] = $data['category_name'];
			$row['category_image'] = $file_path.'images/'.$data['category_image'];
			$row['category_image_thumb'] = $file_path.'images/thumbs/'.$data['category_image'];
			 

			array_push($jsonObj,$row);
		
		}

		$set['LIVETV'] = $jsonObj;	 

  		header( 'Content-Type: application/json; charset=utf-8' );
	    echo $val= str_replace('\\/', '/', json_encode($set,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
		die();
  	}
  	else if($get_method['method_name']=="get_single_series")
  	{
  		$series_id=$get_method['series_id'];
  		$jsonObj= array();

		$sql="SELECT * FROM tbl_series WHERE `status`='1' AND `id`='$series_id'";

		$result = mysqli_query($mysqli,$sql)or die(mysqli_error($mysqli));

		if(mysqli_num_rows($result) > 0){
			$data = mysqli_fetch_assoc($result);
		
			$row['id'] = $data['id'];
			$row['series_name'] = $data['series_name'];
			$row['series_desc'] = addslashes($data['series_desc']);
			$row['series_poster'] = $file_path.'images/series/'.$data['series_poster'];
			$row['series_poster_thumb'] = $file_path.'images/series/thumbs/'.$data['series_poster'];
			$row['series_cover'] = $file_path.'images/series/'.$data['series_cover'];
			$row['series_cover_thumb'] = $file_path.'images/series/thumbs/'.$data['series_cover'];

			$row['total_views'] = $data['total_views'];
			$row['total_rate'] = $data['total_rate'];
			$row['rate_avg'] = $data['rate_avg'];

			// for seasons
			$sql="SELECT * FROM tbl_season WHERE `status`='1' AND `series_id` = '$series_id'";

			$result_2=mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli));

			while($data_2 = mysqli_fetch_assoc($result_2))
			{
				$row2['id'] = $data_2['id'];
				$row2['season_name'] = $data_2['season_name'];

				$season_data[]=$row2;

			}

			if(isset($season_data)!='')
			{
				$row['seasons']=$season_data;
			}
			else
			{
				$row['seasons']=[];
			}

			// for related tv searies
			$sql="SELECT * FROM tbl_series WHERE `status`='1' AND `id` <> '$series_id' ORDER BY `id` DESC LIMIT 0,5";

			$result_3=mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli));

			while($data_3 = mysqli_fetch_assoc($result_3))
			{
				$row3['id'] = $data_3['id'];
				$row3['series_name'] = $data_3['series_name'];
				$row3['series_desc'] = addslashes($data_3['series_desc']);
				$row3['series_poster'] = $file_path.'images/series/'.$data_3['series_poster'];
				$row3['series_poster_thumb'] = $file_path.'images/series/thumbs/'.$data_3['series_poster'];
				$row3['series_cover'] = $file_path.'images/series/'.$data_3['series_cover'];
				$row3['series_cover_thumb'] = $file_path.'images/series/thumbs/'.$data_3['series_cover'];

				$row3['total_views'] = $data_3['total_views'];
				$row3['total_rate'] = $data_3['total_rate'];
				$row3['rate_avg'] = $data_3['rate_avg'];

				$related_data[]=$row3;

			}

			if(isset($related_data)!='')
			{
				$row['related']=$related_data;
			}
			else
			{
				$row['related']=[];
			}

			$row3=array();
			//Comments
			$query_2="SELECT comment.*, user.`id` AS user_id, user.`name` FROM tbl_comments comment, tbl_users user
			WHERE comment.`user_id`=user.`id` AND comment.`post_id`='$series_id' AND comment.`type`='series' ORDER BY comment.`id` DESC LIMIT 0,5";

			$sql2 = mysqli_query($mysqli,$query_2)or die(mysqli_error($mysqli));
				
			while($data_3 = mysqli_fetch_assoc($sql2))
			{
				$row3['id'] = $data_3['id'];
				$row3['post_id'] = $data_3['post_id'];
				$row3['user_id'] = $data_3['user_id'];
	 			$row3['user_name'] = get_user_info($data_3['user_id']);	
	 			$row3['comment_text'] = stripslashes($data_3['comment_text']);

			    $row3['comment_date'] = date('d M, Y',$data_3['comment_on']);

				$comment_data[]=$row3;

			}
			
			if(isset($comment_data)!='')
			{
				$row['comments']=$comment_data;
			}
			else
			{
				$row['comments']=[];
			}

			$view_qry=mysqli_query($mysqli,"UPDATE tbl_series SET `total_views` = total_views + 1 WHERE id = '$series_id'");

			array_push($jsonObj,$row);

		}
		

		$set['LIVETV'] = $jsonObj;

  		header( 'Content-Type: application/json; charset=utf-8' );
	    echo $val= str_replace('\\/', '/', json_encode($set,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
		die();
  	}
  	else if($get_method['method_name']=="get_single_movie")
  	{
  		$movie_id=$get_method['movie_id'];
  		$jsonObj= array();

		$sql="SELECT movie.*, lang.`language_name`, lang.`language_background` FROM tbl_movies movie
				LEFT JOIN tbl_language lang ON movie.`language_id`=lang.`id`
				LEFT JOIN tbl_genres genres ON movie.`genre_id`=genres.`gid`
				WHERE movie.`status`='1' AND lang.`status`='1' AND movie.`id`='$movie_id' ORDER BY movie.`id` DESC";

		$result = mysqli_query($mysqli,$sql)or die(mysqli_error($mysqli));

		if(mysqli_num_rows($result) > 0){
			$data = mysqli_fetch_assoc($result);
		
			$row['id'] = $data['id'];
			$row['language_id'] = $data['language_id'];
			$row['genre_id'] = $data['genre_id'];
			$row['movie_title'] = $data['movie_title'];
			$row['movie_desc'] = addslashes($data['movie_desc']);

			$row['movie_poster'] = $file_path.'images/movies/'.$data['movie_poster'];
			$row['movie_poster_thumb'] = $file_path.'images/movies/thumbs/'.$data['movie_poster'];

			$row['movie_cover'] = $file_path.'images/movies/'.$data['movie_cover'];
			$row['movie_cover_thumb'] = $file_path.'images/movies/thumbs/'.$data['movie_cover'];

			if($data['movie_type']=='local'){
				$row['video_type'] = 'local_url';
			}else{
				$row['video_type'] = $data['movie_type'];	
			}

			if($data['movie_type']=='local'){
				$row['movie_url'] = $file_path.'uploads/'.$data['movie_url'];				
			}else{
				$row['movie_url'] = $data['movie_url'];				
			}

			$row['total_views'] = $data['total_views'];
			$row['total_rate'] = $data['total_rate'];
			$row['rate_avg'] = $data['rate_avg'];

			$row['language_name'] = $data['language_name'];
			$row['language_background'] = '#'.$data['language_background'];

			$sql="SELECT movie.*, lang.`language_name`, lang.`language_background` FROM tbl_movies movie
				LEFT JOIN tbl_language lang ON movie.`language_id`=lang.`id`
				LEFT JOIN tbl_genres genres ON movie.`genre_id`=genres.`gid`
				WHERE movie.`status`='1' AND lang.`status`='1' AND movie.`language_id`='$data[language_id]' AND movie.`id` <> '$movie_id' ORDER BY movie.`id` DESC LIMIT 0,5";

			$result_1=mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli));

			while($data_2 = mysqli_fetch_assoc($result_1))
			{
				$row2['id'] = $data_2['id'];
				$row2['language_id'] = $data_2['language_id'];
				$row2['genre_id'] = $data_2['genre_id'];
				$row2['movie_title'] = $data_2['movie_title'];
				$row2['movie_desc'] = addslashes($data_2['movie_desc']);

				$row2['movie_poster'] = $file_path.'images/movies/'.$data_2['movie_poster'];
				$row2['movie_poster_thumb'] = $file_path.'images/movies/thumbs/'.$data_2['movie_poster'];

				$row2['movie_cover'] = $file_path.'images/movies/'.$data_2['movie_cover'];
				$row2['movie_cover_thumb'] = $file_path.'images/movies/thumbs/'.$data_2['movie_cover'];

				if($data_2['movie_type']=='local'){
					$row2['video_type'] = 'local_url';
				}else{
					$row2['video_type'] = $data_2['movie_type'];	
				}
				

				if($data_2['movie_type']=='local'){
					$row2['movie_url'] = $file_path.'uploads/'.$data_2['movie_url'];				
				}else{
					$row2['movie_url'] = $data_2['movie_url'];				
				}

				

				$row2['total_views'] = $data_2['total_views'];
				$row2['total_rate'] = $data_2['total_rate'];
				$row2['rate_avg'] = $data_2['rate_avg'];

				$row2['language_name'] = $data_2['language_name'];
				$row2['language_background'] = '#'.$data_2['language_background'];

				$related_data[]=$row2;

			}

			if(isset($related_data)!='')
			{
				$row['related']=$related_data;
			}
			else
			{
				$row['related']=[];
			}

			//Comments
			$query_2="SELECT comment.*, user.`id` AS user_id, user.`name` FROM tbl_comments comment, tbl_users user
			WHERE comment.`user_id`=user.`id` AND comment.`post_id`='$movie_id' AND comment.`type`='movie' ORDER BY comment.`id` DESC LIMIT 0,5";

			$sql2 = mysqli_query($mysqli,$query_2)or die(mysqli_error($mysqli));
				
			while($data_3 = mysqli_fetch_assoc($sql2))
			{
				$row3['id'] = $data_3['id'];
				$row3['post_id'] = $data_3['post_id'];
				$row3['user_id'] = $data_3['user_id'];
	 			$row3['user_name'] = get_user_info($data_3['user_id']);	
	 			$row3['comment_text'] = stripslashes($data_3['comment_text']);

			    $row3['comment_date'] = date('d M, Y',$data_3['comment_on']);

				$comment_data[]=$row3;

			}
			
			if(isset($comment_data)!='')
			{
				$row['comments']=$comment_data;
			}
			else
			{
				$row['comments']=[];
			}

			$view_qry=mysqli_query($mysqli,"UPDATE tbl_movies SET `total_views` = total_views + 1 WHERE id = '$movie_id'");

			array_push($jsonObj,$row);

		}

		

		

		

		$set['LIVETV'] = $jsonObj;

  		header( 'Content-Type: application/json; charset=utf-8' );
	    echo $val= str_replace('\\/', '/', json_encode($set,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
		die();
  	}
  	else if($get_method['method_name']=="get_single_channel")
  	{		
  			$SQL1="select * from tbl_channels
		 		LEFT JOIN tbl_category ON tbl_channels.cat_id= tbl_category.cid 
		 		where id='".$get_method['channel_id']."'";	
			
			$result1 = mysqli_query($mysqli,$SQL1)or die(mysqli_error($mysqli));	
						
			$jsonObj= array();

			while ($row1 = mysqli_fetch_assoc($result1)) 
			{
	  
					$catArr=array();
					$catArr['id'] = $row1['id'];
					$catArr['cat_id'] = $row1['cat_id'];
					$catArr['channel_type'] = $row1['channel_type'];
					$catArr['channel_title'] = $row1['channel_title'];
					$catArr['channel_url'] = $row1['channel_url'];
					$catArr['channel_type_ios'] = $row1['channel_type_ios'];
					$catArr['channel_url_ios'] = $row1['channel_url_ios'];
					$catArr['channel_poster'] = $file_path.'images/'.$row1['channel_poster'];
					$catArr['channel_thumbnail'] = $file_path.'images/'.$row1['channel_thumbnail'];
					$catArr['channel_desc'] = $row1['channel_desc'];
					$catArr['total_views'] = $row1['total_views'];
					$catArr['total_rate'] = $row1['total_rate'];
					$rate_avg=floor($row1['rate_avg']);
					$catArr['rate_avg'] = "$rate_avg";

					$catArr['category_name'] = $row1['category_name'];
					
					$SQL2 = "SELECT * FROM tbl_channels WHERE tbl_channels.`status`='1' AND cat_id = '".$row1['cat_id']."' LIMIT 0,5";
	       			$result2 = mysqli_query($mysqli,$SQL2);
					
					$subvidArr=array();
					while ($row2 = mysqli_fetch_assoc($result2)) 
					{	
	 					if($row1['id'] != $row2['id'])
						{									
						$temp = array('rel_id' => $row2['id'], 'rel_channel_title' => $row2['channel_title'] , 'rel_channel_url' =>$row2['channel_url'],'rel_channel_url_ios' => $row2['channel_url_ios']
							,'rel_channel_thumbnail' => $file_path.'images/'.$row2['channel_thumbnail']);
						$subvidArr[]=$temp;
						}
					}
					$catArr['related']=$subvidArr;	


	  				//Comments
					$query_2="SELECT comment.*, user.`id` AS user_id, user.`name` FROM tbl_comments comment, tbl_users user
					WHERE comment.`user_id`=user.`id` AND comment.`post_id`='".$get_method['channel_id']."' AND comment.`type`='channel' ORDER BY comment.`id` DESC LIMIT 0,5";

					$sql2 = mysqli_query($mysqli,$query_2)or die(mysqli_error($mysqli));
						
					while($data_3 = mysqli_fetch_assoc($sql2))
					{
						$row3['id'] = $data_3['id'];
						$row3['post_id'] = $data_3['post_id'];
						$row3['user_id'] = $data_3['user_id'];
			 			$row3['user_name'] = get_user_info($data_3['user_id']);	
			 			$row3['comment_text'] = stripslashes($data_3['comment_text']);

					    $row3['comment_date'] = date('d M, Y',$data_3['comment_on']);

						$comment_data[]=$row3;

					}
					
					if(isset($comment_data)!='')
					{
						$catArr['comments']=$comment_data;
					}
					else
					{
						$catArr['comments']=[];
					}



					array_push($jsonObj,$catArr);

	  
			}

		$view_qry=mysqli_query($mysqli,"UPDATE tbl_channels SET total_views = total_views + 1 WHERE id = '".$get_method['channel_id']."'");
	
				 
				
		$set['LIVETV'] = $jsonObj;
 
  		header( 'Content-Type: application/json; charset=utf-8' );
	    echo $val= str_replace('\\/', '/', json_encode($set,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
		die();
	}

	else if($get_method['method_name']=="get_related_post")
	{	
		$jsonObj= array();	
		$post_id=$get_method['post_id'];
		$type=$get_method['type'];
		$cat_id=$get_method['cat_id'];

		$page_limit=API_PAGE_LIMIT;
			
		$limit=($get_method['page']-1) * $page_limit;

		if($type=='series')
		{
			// for related tv searies
			$sql="SELECT * FROM tbl_series WHERE `status`='1' AND `id` <> '$post_id' ORDER BY `id` DESC LIMIT $limit, $page_limit";

			$result_3=mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli));

			while($data_3 = mysqli_fetch_assoc($result_3))
			{
				$row3['id'] = $data_3['id'];
				$row3['series_name'] = $data_3['series_name'];
				$row3['series_desc'] = addslashes($data_3['series_desc']);
				$row3['series_poster'] = $file_path.'images/series/'.$data_3['series_poster'];
				$row3['series_poster_thumb'] = $file_path.'images/series/thumbs/'.$data_3['series_poster'];
				$row3['series_cover'] = $file_path.'images/series/'.$data_3['series_cover'];
				$row3['series_cover_thumb'] = $file_path.'images/series/thumbs/'.$data_3['series_cover'];

				$row3['total_views'] = $data_3['total_views'];
				$row3['total_rate'] = $data_3['total_rate'];
				$row3['rate_avg'] = $data_3['rate_avg'];

				array_push($jsonObj,$row3);

			}

		}
		else if($type=='movie')
		{
			$sql="SELECT movie.*, lang.`language_name`, lang.`language_background` FROM tbl_movies movie
			LEFT JOIN tbl_language lang ON movie.`language_id`=lang.`id`
			LEFT JOIN tbl_genres genres ON movie.`genre_id`=genres.`gid`
			WHERE movie.`status`='1' AND lang.`status`='1' AND movie.`language_id`='$cat_id' AND movie.`id` <> '$post_id' ORDER BY movie.`id` DESC LIMIT $limit, $page_limit";

			$result_1=mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli));

			while($data_2 = mysqli_fetch_assoc($result_1))
			{
				$row2['id'] = $data_2['id'];
				$row2['language_id'] = $data_2['language_id'];
				$row2['genre_id'] = $data_2['genre_id'];
				$row2['movie_title'] = $data_2['movie_title'];
				$row2['movie_desc'] = addslashes($data_2['movie_desc']);

				$row2['movie_poster'] = $file_path.'images/movies/'.$data_2['movie_poster'];
				$row2['movie_poster_thumb'] = $file_path.'images/movies/thumbs/'.$data_2['movie_poster'];

				$row2['movie_cover'] = $file_path.'images/movies/'.$data_2['movie_cover'];
				$row2['movie_cover_thumb'] = $file_path.'images/movies/thumbs/'.$data_2['movie_cover'];

				if($data_2['movie_type']=='local'){
					$row2['video_type'] = 'local_url';
				}else{
					$row2['video_type'] = $data_2['movie_type'];	
				}
				

				if($data_2['movie_type']=='local'){
					$row2['movie_url'] = $file_path.'uploads/'.$data_2['movie_url'];				
				}else{
					$row2['movie_url'] = $data_2['movie_url'];				
				}

				$row2['total_views'] = $data_2['total_views'];
				$row2['total_rate'] = $data_2['total_rate'];
				$row2['rate_avg'] = $data_2['rate_avg'];

				$row2['language_name'] = $data_2['language_name'];
				$row2['language_background'] = '#'.$data_2['language_background'];

				array_push($jsonObj,$row2);

			}

		}
		else if($type=='channel')
		{
			$SQL2 = "SELECT * FROM tbl_channels WHERE tbl_channels.`status`='1' AND cat_id = '$cat_id' AND tbl_channels.`id` <> '$post_id' ORDER BY tbl_channels.`id` DESC LIMIT $limit, $page_limit";

   			$result2 = mysqli_query($mysqli,$SQL2) or die(mysqli_error($mysqli_error));
			
			while ($row2 = mysqli_fetch_assoc($result2)) 
			{	
				
				$temp =array(
						'rel_id' => $row2['id'], 
						'rel_channel_title' => $row2['channel_title'] , 
						'rel_channel_url' =>$row2['channel_url'],
						'rel_channel_url_ios' => $row2['channel_url_ios'],
						'rel_channel_thumbnail' => $file_path.'images/'.$row2['channel_thumbnail']
					);

				array_push($jsonObj,$temp);
				
			}
			
		}

		$set['LIVETV'] = $jsonObj;
		header( 'Content-Type: application/json; charset=utf-8' );
	    echo $val= str_replace('\\/', '/', json_encode($set,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
		die();

	}
	else if($get_method['method_name']=="get_search_series")
	{	
		
		$jsonObj= array();	

		$search_text=trim($get_method['search_text']);

		$page_limit=API_PAGE_LIMIT;
			
		$limit=($get_method['page']-1) * $page_limit;

		$sql="SELECT * FROM tbl_series WHERE `status`='1' AND (`series_name` LIKE '%$search_text%' OR `series_desc` LIKE '%$search_text%') ORDER BY `id` DESC LIMIT $limit, $page_limit";

		$result=mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli));

		if(mysqli_num_rows($result) > 0){
			
			while($data = mysqli_fetch_assoc($result))
			{
				$row3['id'] = $data['id'];
				$row3['series_name'] = $data['series_name'];
				$row3['series_desc'] = addslashes($data['series_desc']);
				$row3['series_poster'] = $file_path.'images/series/'.$data['series_poster'];
				$row3['series_poster_thumb'] = $file_path.'images/series/thumbs/'.$data['series_poster'];
				$row3['series_cover'] = $file_path.'images/series/'.$data['series_cover'];
				$row3['series_cover_thumb'] = $file_path.'images/series/thumbs/'.$data['series_cover'];

				$row3['total_views'] = $data['total_views'];
				$row3['total_rate'] = $data['total_rate'];
				$row3['rate_avg'] = $data['rate_avg'];

				array_push($jsonObj,$row3);

			}
			$set['LIVETV'] = $jsonObj;
				 
		}
		else
		{
			$set['LIVETV'] = $jsonObj;

		}

		header( 'Content-Type: application/json; charset=utf-8' );
	    echo $val= str_replace('\\/', '/', json_encode($set,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
		die();
	}
	else if($get_method['method_name']=="get_search_movies")
	{	
		
		$jsonObj= array();	

		$page_limit=API_PAGE_LIMIT;
			
		$limit=($get_method['page']-1) * $page_limit;

		$search_text=trim($get_method['search_text']);

		$sql="SELECT movie.*, lang.`language_name`, lang.`language_background` FROM tbl_movies movie
			LEFT JOIN tbl_language lang ON movie.`language_id`=lang.`id`
			LEFT JOIN tbl_genres genres ON movie.`genre_id`=genres.`gid`
			WHERE movie.`status`='1' AND lang.`status`='1' AND (lang.`language_name` LIKE '%$search_text%' OR genres.`genre_name` LIKE '%$search_text%' OR movie.`movie_title` LIKE '%$search_text%' OR movie.`movie_desc` LIKE '%$search_text%') ORDER BY movie.`id` DESC LIMIT $limit, $page_limit";

		$result=mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli));

		if(mysqli_num_rows($result) > 0){
			while($data = mysqli_fetch_assoc($result))
			{
				$row['id'] = $data['id'];
				$row['language_id'] = $data['language_id'];
				$row['genre_id'] = $data['genre_id'];
				$row['movie_title'] = $data['movie_title'];
				$row['movie_desc'] = addslashes($data['movie_desc']);

				$row['movie_poster'] = $file_path.'images/movies/'.$data['movie_poster'];
				$row['movie_poster_thumb'] = $file_path.'images/movies/thumbs/'.$data['movie_poster'];

				$row['movie_cover'] = $file_path.'images/movies/'.$data['movie_cover'];
				$row['movie_cover_thumb'] = $file_path.'images/movies/thumbs/'.$data['movie_cover'];

				if($data['movie_type']=='local'){
					$row['video_type'] = 'local_url';
				}else{
					$row['video_type'] = $data['movie_type'];	
				}
				

				if($data['movie_type']=='local'){
					$row['movie_url'] = $file_path.'uploads/'.$data['movie_url'];				
				}else{
					$row['movie_url'] = $data['movie_url'];				
				}

				$row['total_views'] = $data['total_views'];
				$row['total_rate'] = $data['total_rate'];
				$row['rate_avg'] = $data['rate_avg'];

				$row['language_name'] = $data['language_name'];
				$row['language_background'] = '#'.$data['language_background'];

				array_push($jsonObj,$row);

			}
			$set['LIVETV'] = $jsonObj;
				 
		}
		else
		{
			$set['LIVETV'] = $jsonObj;

		}

		header( 'Content-Type: application/json; charset=utf-8' );
	    echo $val= str_replace('\\/', '/', json_encode($set,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
		die();
	}
	else if($get_method['method_name']=="get_search_channels")
	{	
		
		$jsonObj= array();	

		$page_limit=API_PAGE_LIMIT;
			
		$limit=($get_method['page']-1) * $page_limit;

		$query="SELECT * FROM tbl_channels
			LEFT JOIN tbl_category ON tbl_channels.cat_id= tbl_category.cid 
			WHERE tbl_channels.status=1 AND (channel_title like '%".$get_method['search_text']."%' OR channel_desc like '%".$get_method['search_text']."%') LIMIT $limit, $page_limit";
		$sql = mysqli_query($mysqli,$query);
		
		if(mysqli_num_rows($sql)>0){

				while($data = mysqli_fetch_assoc($sql))
				{
					$row['id'] = $data['id'];
					$row['cat_id'] = $data['cat_id'];
					$row['channel_title'] = $data['channel_title'];
					$row['channel_url'] = $data['channel_url'];
					$row['channel_url_ios'] = $data['channel_url_ios'];
					$row['channel_thumbnail'] = $file_path.'images/'.$data['channel_thumbnail'];
					$row['channel_desc'] = $data['channel_desc'];

					$row['rate_avg'] = $data['rate_avg'];

					$row['cid'] = $data['cid'];
					$row['category_name'] = $data['category_name'];
					$row['category_image'] = $file_path.'images/'.$data['category_image'];
					$row['category_image_thumb'] = $file_path.'images/thumbs/'.$data['category_image'];
					 

					array_push($jsonObj,$row);
				
				}

				$set['LIVETV'] = $jsonObj;
				 
		}
		else
		{
			$set['LIVETV'] = $jsonObj;
		}

		header( 'Content-Type: application/json; charset=utf-8' );
	    echo $val= str_replace('\\/', '/', json_encode($set,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
		die();
	}
	else if($get_method['method_name']=="user_report")
	{		
		
		$report=$get_method['report']; 
		$type = $get_method['type'];

		if($report!=''){
			$data = array(
		 	    'user_id'  => $get_method['user_id'],
				'post_id'  => $get_method['post_id'],
				'report'  =>  $report,
				'type'  =>  $type,
				'report_on'  =>  strtotime(date('d-m-Y h:i:s A'))
			);

			$qry = Insert('tbl_reports',$data);
			$set['LIVETV'][] = array('msg' => 'Report has been sent successfully...','success'=>'1');
		}
		else{
			$set['LIVETV'][] = array('msg' => 'Please enter report text','success'=>'0');
		}

		header( 'Content-Type: application/json; charset=utf-8' );
	    echo $val= str_replace('\\/', '/', json_encode($set,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
		die();
	}
	else if($get_method['method_name']=="user_register")
	{	
			if($get_method['name']!='' AND $get_method['email']!='' AND $get_method['password']!='')
		   {

			$qry = "SELECT * FROM tbl_users WHERE email = '".$get_method['email']."'"; 
			$result = mysqli_query($mysqli,$qry);
			$row = mysqli_fetch_assoc($result);
			
			if($row['email']!="")
			{
				$set['LIVETV'][]=array('msg' => "Email address already used!",'success'=>'0');
			}
			else
			{ 
	 			$qry1="INSERT INTO tbl_users (`user_type`,`name`,`email`,`password`,`phone`,`status`) VALUES ('Normal','".$get_method['name']."','".$get_method['email']."','".$get_method['password']."','".$get_method['phone']."','1')"; 
	            
	            $result1=mysqli_query($mysqli,$qry1);  										 
						 
					
				$set['LIVETV'][]=array('msg' => "Register successflly...!",'success'=>'1');
						
			}
	  

		}
		else
		{
			$set['LIVETV'][]=array('msg' => "Empty fields!",'success'=>'0');
		}

		header( 'Content-Type: application/json; charset=utf-8' );
	    echo $val= str_replace('\\/', '/', json_encode($set,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
		die();
	}
	else if($get_method['method_name']=="user_login")
	{

		$email = $get_method['email'];
		$password = $get_method['password'];

		$qry = "SELECT * FROM tbl_users WHERE email = '".$email."' and password = '".$password."'"; 
		$result = mysqli_query($mysqli,$qry);
		$num_rows = mysqli_num_rows($result);
		$row = mysqli_fetch_assoc($result);
		
		if ($num_rows > 0)
		{ 
			
			if($row['status']==1)		 
				$set['LIVETV'][]=array('user_id' => $row['id'],'name'=>$row['name'],'success'=>'1');
			else
			    $set['LIVETV'][]=array('msg' =>'Admin was disable or deleted you as user !','success'=>'0');  
			 
		}		 
		else
		{
			$set['LIVETV'][]=array('msg' =>'Login failed','success'=>'0');		 
		}

		header( 'Content-Type: application/json; charset=utf-8' );
	    echo $val= str_replace('\\/', '/', json_encode($set,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
		die();

	}
	else if($get_method['method_name']=="user_profile")
	{
			$qry = "SELECT * FROM tbl_users WHERE id = '".$get_method['user_id']."'"; 
		$result = mysqli_query($mysqli,$qry);
		 
		$row = mysqli_fetch_assoc($result);
	  				 
	    $set['LIVETV'][]=array('user_id' => $row['id'],'name'=>$row['name'],'email'=>$row['email'],'phone'=>$row['phone'],'success'=>'1');

	    header( 'Content-Type: application/json; charset=utf-8' );
	    echo $val= str_replace('\\/', '/', json_encode($set,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
		die();
	}
	else if($get_method['method_name']=="user_profile_update")
	{
			if($get_method['password']!="")
		{
			$user_edit= "UPDATE tbl_users SET name='".$get_method['name']."',email='".$get_method['email']."',password='".$get_method['password']."',phone='".$get_method['phone']."' WHERE id = '".$get_method['user_id']."'";	 
		}
		else
		{
			$user_edit= "UPDATE tbl_users SET name='".$get_method['name']."',email='".$get_method['email']."',phone='".$get_method['phone']."' WHERE id = '".$get_method['user_id']."'";	 
		}
			
		$user_res = mysqli_query($mysqli,$user_edit);
			 
	  				 
		$set['LIVETV'][]=array('msg'=>'Updated','success'=>'1');

			 header( 'Content-Type: application/json; charset=utf-8' );
	    echo $val= str_replace('\\/', '/', json_encode($set,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
		die();
	}
	else if($get_method['method_name']=="forgot_pass")
	{
		$host = $_SERVER['HTTP_HOST'];
		preg_match("/[^\.\/]+\.[^\.\/]+$/", $host, $matches);
	    $domain_name=$matches[0];
	     
	 	 
		$qry = "SELECT * FROM tbl_users WHERE email = '".$get_method['user_email']."'"; 
		$result = mysqli_query($mysqli,$qry);
		$row = mysqli_fetch_assoc($result);
		
		if($row['email']!="")
		{

			$to = $row['email'];
			$recipient_name=$row['name'];
			// subject
			$subject = '[IMPORTANT] '.APP_NAME.' Forgot Password Information';
				
			$message='<div style="background-color: #f9f9f9;" align="center"><br />
					  <table style="font-family: OpenSans,sans-serif; color: #666666;" border="0" width="600" cellspacing="0" cellpadding="0" align="center" bgcolor="#FFFFFF">
					    <tbody>
					      <tr>
					        <td colspan="2" bgcolor="#FFFFFF" align="center"><img src="http://'.$_SERVER['SERVER_NAME'] . dirname($_SERVER['REQUEST_URI']).'/images/'.APP_LOGO.'" alt="header" /></td>
					      </tr>
					      <tr>
					        <td width="600" valign="top" bgcolor="#FFFFFF"><br>
					          <table style="font-family:OpenSans,sans-serif; color: #666666; font-size: 10px; padding: 15px;" border="0" width="100%" cellspacing="0" cellpadding="0" align="left">
					            <tbody>
					              <tr>
					                <td valign="top"><table border="0" align="left" cellpadding="0" cellspacing="0" style="font-family:OpenSans,sans-serif; color: #666666; font-size: 10px; width:100%;">
					                    <tbody>
					                      <tr>
					                        <td><p style="color: #262626; font-size: 28px; margin-top:0px;"><strong>Dear '.$row['name'].'</strong></p>
					                          <p style="color:#262626; font-size:20px; line-height:32px;font-weight:500;">Thank you for using '.APP_NAME.',<br>
					                            Your password is: '.$row['password'].'</p>
					                          <p style="color:#262626; font-size:20px; line-height:32px;font-weight:500;margin-bottom:30px;">Thanks you,<br />
					                            '.APP_NAME.'.</p></td>
					                      </tr>
					                    </tbody>
					                  </table></td>
					              </tr>
					               
					            </tbody>
					          </table></td>
					      </tr>
					      <tr>
					        <td style="color: #262626; padding: 20px 0; font-size: 20px; border-top:5px solid #52bfd3;" colspan="2" align="center" bgcolor="#ffffff">Copyright © '.APP_NAME.'.</td>
					      </tr>
					    </tbody>
					  </table>
					</div>';


			send_email($to,$recipient_name,$subject,$message);

			 	  
			$set['LIVETV'][]=array('msg' => "Password has been sent on your mail!",'success'=>'1');
		}
		else
		{  	 
				
			$set['LIVETV'][]=array('msg' => "Email not found in our database!",'success'=>'0');
					
		}

	    header( 'Content-Type: application/json; charset=utf-8' );
	    echo $val= str_replace('\\/', '/', json_encode($set,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
		die();
	}
	elseif ($get_method['method_name']=="my_rating") {

		$jsonObj= array();		

		$post_id = $get_method['post_id'];
	  	$user_id = $get_method['user_id'];
	  	$type = $get_method['type'];
	   	
	   	$res=mysqli_query($mysqli,"SELECT * FROM tbl_rating WHERE `user_id`='$user_id' AND `post_id`='$post_id' AND `type`='$type'");

	   	if(mysqli_num_rows($res) > 0){

	   		$usr_rate=mysqli_fetch_assoc($res);
			$jsonObj = array( 'user_rate' => $usr_rate['rate'],'success'=>"1");	
			
	   	}else{

			$jsonObj = array( 'user_rate' => "0",'success'=>"1");
	   	}

		

		$set['LIVETV'][] = $jsonObj;
	    
	    header( 'Content-Type: application/json; charset=utf-8' );
	    echo $val= str_replace('\\/', '/', json_encode($set,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
	    die();  
	}
	else if($get_method['method_name']=="user_rating")
	{
		// $ip = $get_method['device_id'];
		$post_id = $get_method['post_id'];
		$user_id = $get_method['user_id'];
		$therate = $get_method['rate'];
		$type = $get_method['type'];
	  	
	  	$sql="SELECT * FROM tbl_rating where `post_id`  = '$post_id' AND `user_id` = '$user_id' AND `type`='$type'";

		$query1 = mysqli_query($mysqli,$sql); 
		while($data1 = mysqli_fetch_assoc($query1)){
			$rate_db1[] = $data1;
		}
		if(@count($rate_db1) == 0 ){

			$data = array(
		 	    'post_id'  => $post_id,
		 	    'user_id'  => $user_id,
		 	    'ip'  =>  '',
				'rate'  =>  $therate,
				'type'  =>  $type
			);

			$qry = Insert('tbl_rating',$data);

		  	//Total rate result
		   
			$query = mysqli_query($mysqli,"SELECT * FROM tbl_rating WHERE `post_id`='$post_id' AND `type`='$type'");
		       
		 	while($data = mysqli_fetch_assoc($query)){
	            $rate_db[] = $data;
	            $sum_rates[] = $data['rate'];
	       
	        }

	        if(@count($rate_db)){
	            $rate_times = count($rate_db);
	            $sum_rates = array_sum($sum_rates);
	            $rate_value = $sum_rates/$rate_times;
	            $rate_bg = (($rate_value)/5)*100;
	        }else{
	            $rate_times = 0;
	            $rate_value = 0;
	            $rate_bg = 0;
	        }
		 
			$rate_avg=round($rate_value); 

			$tbl_nm='';
			if($type=='channel'){
				$tbl_nm='tbl_channels';
			}
			else if($type=='movie'){
				$tbl_nm='tbl_movies';
			}
			else if($type=='series'){
				$tbl_nm='tbl_series';
			}
			else{
				$set['LIVETV'][]=array('msg' => "Type must be in (channel, movie, series)",'success'=>'0');
			}


			$sql="UPDATE $tbl_nm set total_rate=total_rate + 1,rate_avg='$rate_avg' where id='".$post_id."'";
			mysqli_query($mysqli,$sql);

			$total_rat_sql="SELECT * FROM $tbl_nm WHERE id='".$post_id."'";
			$total_rat_res=mysqli_query($mysqli,$total_rat_sql);
			$total_rat_row=mysqli_fetch_assoc($total_rat_res);

			$rate_avg=floor($total_rat_row['rate_avg']);
			 
			$set['LIVETV'][]=array('total_rate' =>$total_rat_row['total_rate'],'rate_avg' =>"$rate_avg",'msg' => "You have successfully rated",'success'=>'1');

		}else{

			$data = array(
		 	    'post_id'  => $post_id,
		 	    'user_id'  => $user_id,
		 	    'ip'  =>  '',
				'rate'  =>  $therate,
				'type'  =>  $type
			);

			$qry=Update('tbl_rating', $data, "WHERE post_id = '".$post_id."' AND user_id = '".$user_id."' AND type = '".$type."'");

		  	//Total rate result
		   
			$query = mysqli_query($mysqli,"SELECT * FROM tbl_rating WHERE `post_id`='$post_id' AND `type`='$type'");
		       
		 	while($data = mysqli_fetch_assoc($query)){
	            $rate_db[] = $data;
	            $sum_rates[] = $data['rate'];
	       
	        }

	        if(@count($rate_db)){
	            $rate_times = count($rate_db);
	            $sum_rates = array_sum($sum_rates);
	            $rate_value = $sum_rates/$rate_times;
	            $rate_bg = (($rate_value)/5)*100;
	        }else{
	            $rate_times = 0;
	            $rate_value = 0;
	            $rate_bg = 0;
	        }
		 
			$rate_avg=round($rate_value); 

			$tbl_nm='';
			if($type=='channel'){
				$tbl_nm='tbl_channels';
			}
			else if($type=='movie'){
				$tbl_nm='tbl_movies';
			}
			else if($type=='series'){
				$tbl_nm='tbl_series';
			}
			else{
				$set['LIVETV'][]=array('msg' => "Type must be in (channel, movie, series)",'success'=>'0');
			}


			$sql="UPDATE $tbl_nm set rate_avg='$rate_avg' where id='".$post_id."'";
			mysqli_query($mysqli,$sql);

			$total_rat_sql="SELECT * FROM $tbl_nm WHERE id='".$post_id."'";
			$total_rat_res=mysqli_query($mysqli,$total_rat_sql);
			$total_rat_row=mysqli_fetch_assoc($total_rat_res);

			$rate_avg=floor($total_rat_row['rate_avg']);
			 
			$set['LIVETV'][]=array('total_rate' =>$total_rat_row['total_rate'],'rate_avg' =>"$rate_avg",'msg' => "Your rating is successfully updated",'success'=>'1');
			

		}

		header( 'Content-Type: application/json; charset=utf-8' );
		echo $val= str_replace('\\/', '/', json_encode($set,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
		die();
	}
	else if($get_method['method_name']=="user_comment")
	{
		$type = $get_method['type'];
		$comment_text = trim($get_method['comment_text']);

		$post_id=$get_method['post_id'];
		$jsonObj= array();

		$data = array(
	 	    'post_id'  => $get_method['post_id'],
	 	    'user_id'  => $get_method['user_id'],
			'comment_text'  =>  $comment_text,
			'type'  =>  $type,
			'comment_on'  =>  strtotime(date('d-m-Y h:i:s A'))
		);		
	 		
		$qry = Insert('tbl_comments',$data);
		$set['msg']="Comment posted successfully...";
		$set['success']="1";

		if($get_method['is_limit']=='true'){
			//Comments
			$query_2="SELECT comment.*, user.`id` AS user_id, user.`name` FROM tbl_comments comment, tbl_users user
			WHERE comment.`user_id`=user.`id` AND comment.`post_id`='$post_id' AND comment.`type`='$type' ORDER BY comment.`id` DESC LIMIT 0,5";

			$sql2 = mysqli_query($mysqli,$query_2)or die(mysqli_error($mysqli));
				
			while($data_3 = mysqli_fetch_assoc($sql2))
			{
				$row3['id'] = $data_3['id'];
				$row3['post_id'] = $data_3['post_id'];
				$row3['user_id'] = $data_3['user_id'];
	 			$row3['user_name'] = get_user_info($data_3['user_id']);	
	 			$row3['comment_text'] = stripslashes($data_3['comment_text']);

			    $row3['comment_date'] = date('d M, Y',$data_3['comment_on']);

				array_push($jsonObj, $row3);

			}
		}else{
			//Comments
			$query_2="SELECT comment.*, user.`id` AS user_id, user.`name` FROM tbl_comments comment, tbl_users user
			WHERE comment.`user_id`=user.`id` AND comment.`post_id`='$post_id' AND comment.`type`='$type' ORDER BY comment.`id` DESC";

			$sql2 = mysqli_query($mysqli,$query_2)or die(mysqli_error($mysqli));
				
			while($data_3 = mysqli_fetch_assoc($sql2))
			{
				$row3['id'] = $data_3['id'];
				$row3['post_id'] = $data_3['post_id'];
				$row3['user_id'] = $data_3['user_id'];
	 			$row3['user_name'] = get_user_info($data_3['user_id']);	
	 			$row3['comment_text'] = stripslashes($data_3['comment_text']);

			    $row3['comment_date'] = date('d M, Y',$data_3['comment_on']);

				array_push($jsonObj, $row3);

			}
		}

		$set['LIVETV']=$jsonObj;
		
		header( 'Content-Type: application/json; charset=utf-8' );
		echo $val= str_replace('\\/', '/', json_encode($set,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
		die();
	}
	else if($get_method['method_name']=="get_user_comment")
	{
		$post_id=$get_method['post_id'];
		$type=$get_method['type'];

		$sql="SELECT * FROM tbl_comments where `post_id`='$post_id' AND `type`='$type' ORDER BY id DESC";	

		$result = mysqli_query($mysqli,$sql)or die(mysqli_error($mysqli));	
						
		$jsonObj= array();

		while ($data = mysqli_fetch_assoc($result)) 
		{
			$row['id'] = $data['id'];
			$row['post_id'] = $data['post_id'];
			$row['user_name'] = get_user_info($data['user_id']);
			$row['comment_text'] = $data['comment_text'];
			$row['comment_date'] = date('d M, Y',$data['comment_on']); 

			array_push($jsonObj,$row);
  
		}
				
		$set['LIVETV'] = $jsonObj;			
		header( 'Content-Type: application/json; charset=utf-8' );
		echo $val= str_replace('\\/', '/', json_encode($set,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
		die();
	}
	else if($get_method['method_name']=="get_videos")
	{
			$jsonObj= array();	

		$query="SELECT * FROM tbl_video
			WHERE tbl_video.status='1' ORDER BY tbl_video.id DESC";

		$sql = mysqli_query($mysqli,$query)or die(mysqli_error($mysqli));

		while($data = mysqli_fetch_assoc($sql))
		{
			$row['id'] = $data['id'];
			$row['video_type'] = $data['video_type'];
			$row['video_title'] = $data['video_title'];
			$row['video_url'] = $data['video_url'];
			$row['video_id'] = $data['video_id'];

			 
			$row['video_thumbnail_b'] = $file_path.'images/'.$data['video_thumbnail'];
			$row['video_thumbnail_s'] = $file_path.'images/thumbs/'.$data['video_thumbnail'];
			  
				$row['total_views'] = $data['total_views']; 	

			array_push($jsonObj,$row);
		
		}
		
		$set['LIVETV'] = $jsonObj;

		header( 'Content-Type: application/json; charset=utf-8' );
	    echo $val= str_replace('\\/', '/', json_encode($set,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
		die();	
	}
	else if($get_method['method_name']=="get_single_video")
	{
			$jsonObj= array();	

		$query="SELECT * FROM tbl_video
			WHERE tbl_video.id='".$get_method['video_id']."'";

		$sql = mysqli_query($mysqli,$query)or die(mysqli_error($mysqli));

		while($data = mysqli_fetch_assoc($sql))
		{ 
			 
			$row['id'] = $data['id'];
			$row['video_type'] = $data['video_type'];
			$row['video_title'] = $data['video_title'];
			$row['video_url'] = $data['video_url'];
			$row['video_id'] = $data['video_id'];
			
			$row['video_thumbnail_b'] = $file_path.'images/'.$data['video_thumbnail'];
			$row['video_thumbnail_s'] = $file_path.'images/thumbs/'.$data['video_thumbnail'];
			$row['total_views'] = $data['total_views']; 

			array_push($jsonObj,$row);
		
		}

		$view_qry=mysqli_query($mysqli,"UPDATE tbl_video SET total_views = total_views + 1 WHERE id = '".$get_method['video_id']."'");

		
		$set['LIVETV'] = $jsonObj;

		header( 'Content-Type: application/json; charset=utf-8' );
	    echo $val= str_replace('\\/', '/', json_encode($set,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
		die();	
	}
	else if($get_method['method_name']=="get_app_details")
	{
			$jsonObj= array();	

			$query="SELECT * FROM tbl_settings WHERE id='1'";
			$sql = mysqli_query($mysqli,$query)or die(mysqli_error($mysqli));

			if($get_method['user_id']!=''){
				$set['user_status']=get_user_status($get_method['user_id']);
			}
			else{
				$set['user_status']="false";	
			}

			while($data = mysqli_fetch_assoc($sql))
			{

			$row['package_name'] = $data['package_name']; 
			$row['ios_bundle_identifier'] = $data['ios_bundle_identifier'];  
			$row['app_name'] = $data['app_name'];
			$row['app_logo'] = $data['app_logo'];
			$row['app_version'] = $data['app_version'];
			$row['app_author'] = $data['app_author'];
			$row['app_contact'] = $data['app_contact'];
			$row['app_email'] = $data['app_email'];
			$row['app_website'] = $data['app_website'];
			$row['app_description'] = $data['app_description'];
			$row['app_developed_by'] = $data['app_developed_by'];

			$row['app_privacy_policy'] = stripslashes($data['app_privacy_policy']);

			$row['publisher_id'] = $data['publisher_id'];
			$row['interstital_ad'] = $data['interstital_ad'];
			$row['interstital_ad_id'] = $data['interstital_ad_id'];
			$row['interstital_ad_click'] = $data['interstital_ad_click'];
			$row['banner_ad'] = $data['banner_ad'];
			$row['banner_ad_id'] = $data['banner_ad_id'];

			$row['publisher_id_ios'] = $data['publisher_id_ios'];
			$row['app_id_ios'] = $data['app_id_ios'];
			$row['interstital_ad_ios'] = $data['interstital_ad_ios'];
			$row['interstital_ad_id_ios'] = $data['interstital_ad_id_ios'];
			$row['interstital_ad_click_ios'] = $data['interstital_ad_click_ios'];
			$row['banner_ad_ios'] = $data['banner_ad_ios'];
			$row['banner_ad_id_ios'] = $data['banner_ad_id_ios'];

			

			array_push($jsonObj,$row);
		
		}

		$set['LIVETV'] = $jsonObj;
		
		header( 'Content-Type: application/json; charset=utf-8' );
	    echo $val= str_replace('\\/', '/', json_encode($set,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
		die();
	}  
	else
	{
		$get_method = checkSignSalt($_POST['data']);
	}

?>