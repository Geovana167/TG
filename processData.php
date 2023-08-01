<?php 
	require("includes/connection.php");
	require("includes/function.php");
	require("language/language.php");

	$response=array();

	// get total comments
	function total_comments($news_id)
	{
		global $mysqli;

		$query="SELECT COUNT(*) AS total_comments FROM tbl_comments WHERE `news_id`='$news_id'";
		$sql = mysqli_query($mysqli,$query)or die(mysqli_error());
		$row=mysqli_fetch_assoc($sql);
		return stripslashes($row['total_comments']);
	}


	switch ($_POST['action']) {
		case 'toggle_status':
			$id=$_POST['id'];
			$for_action=$_POST['for_action'];
			$column=$_POST['column'];
			$tbl_id=$_POST['tbl_id'];
			$table_nm=$_POST['table'];

			if($for_action=='active'){
				$data = array($column  =>  '1');
			    $edit_status=Update($table_nm, $data, "WHERE $tbl_id = '$id'");
			}else{
				$data = array($column  =>  '0');
			    $edit_status=Update($table_nm, $data, "WHERE $tbl_id = '$id'");
			}
			
	      	$response['status']=1;
	      	$response['action']=$for_action;
	      	echo json_encode($response);
			break;

		case 'removeComment':
			$id=$_POST['id'];

			Delete('tbl_comments','id='.$id);


	      	$response['status']=1;
	      	echo json_encode($response);
			break;

		case 'removeAllComment':
			$news_id=$_POST['news_id'];

			Delete('tbl_comments','news_id='.$news_id);


	      	$response['status']=1;
	      	echo json_encode($response);
			break;

		case 'getSeason':
			$id=$_POST['id'];

			$sql="SELECT * FROM tbl_season WHERE series_id='$id'";
			$res=mysqli_query($mysqli,$sql);
			$result=array();
			while ($row=mysqli_fetch_assoc($res)) {
				$opt='<option value="'.$row['id'].'">'.$row['season_name'].'</option>';
				array_push($result, $opt);
			}

	      	$response['status']=1;
	      	$response['data']=$result;
	      	echo json_encode($response);
			break;

		case 'multi_delete':

			$ids=implode(",", $_POST['id']);

			if($ids==''){
				$ids=$_POST['id'];
			}

			$tbl_nm=$_POST['tbl_nm'];

			if($tbl_nm=='tbl_movies'){
				$sql="SELECT * FROM $tbl_nm WHERE `id` IN ($ids)";
				$res=mysqli_query($mysqli, $sql);
				while ($row=mysqli_fetch_assoc($res)){
					if($row['movie_cover']!="")
					{
						unlink('images/movies/'.$row['movie_cover']);
						unlink('images/movies/thumbs/'.$row['movie_cover']);
					}

					if($row['movie_poster']!="")
					{
						unlink('images/movies/'.$row['movie_poster']);
						unlink('images/movies/thumbs/'.$row['movie_poster']);
					}

					if($row['movie_type']=="local")
					{
						unlink('uploads/'.$row['movie_url']);
					}

				}
				$deleteSql="DELETE FROM $tbl_nm WHERE `id` IN ($ids)";
			}

			else if($tbl_nm=='tbl_episode'){
				$sql="SELECT * FROM $tbl_nm WHERE `id` IN ($ids)";
				$res=mysqli_query($mysqli, $sql);
				while ($row=mysqli_fetch_assoc($res)){
					if($row['episode_poster']!="")
					{
						unlink('images/episodes/'.$row['episode_poster']);
						unlink('images/episodes/thumbs/'.$row['episode_poster']);
					}

					if($row['episode_type']=="local")
					{
						unlink('uploads/'.$row['episode_url']);
					}

				}
				$deleteSql="DELETE FROM $tbl_nm WHERE `id` IN ($ids)";

			}

			else if($tbl_nm=='tbl_channels'){
				$sql="SELECT * FROM $tbl_nm WHERE `id` IN ($ids)";
				$res=mysqli_query($mysqli, $sql);
				while ($row=mysqli_fetch_assoc($res)){

					if($row['channel_thumbnail']!="")
				    {
				      	unlink('images/'.$row['channel_thumbnail']);
				  	  	unlink('images/thumbs/'.$row['channel_thumbnail']);

				  	}

				}
				$deleteSql="DELETE FROM $tbl_nm WHERE `id` IN ($ids)";

			}


			mysqli_query($mysqli, $deleteSql);
			
	      	$response['status']=1;
	      	echo json_encode($response);
			break;
		
		default:
			# code...
			break;
	}

?>