<?php include("includes/header.php");

  $file_path = $protocol.'://'.$_SERVER['SERVER_NAME'] . dirname($_SERVER['REQUEST_URI']).'/api.php';

?>
<div class="row">
      <div class="col-sm-12 col-xs-12">
     	 	<div class="card">
		        <div class="card-header">
		          Example API urls
		        </div>
       			    <div class="card-body no-padding">
         		
         			 <pre>
                <code class="html">
                <br><b>API URL</b>&nbsp; <?php echo $file_path;?>    

                <br><b>Home</b>(Method: get_home)
                <br><b>Latest Channels</b>(Method: get_latest_channels)(Parameter: page)
                <br><b>Latest Movies</b>(Method: get_latest_movies)(Parameter: page)
                <br><b>Category List</b>(Method: get_category)(Parameter: page)
                <br><b>Language List</b>(Method: get_language)(Parameter: page)
                <br><b>Genre List</b>(Method: get_genre)(Parameter: page)
                <br><b>Movie List</b>(Method: get_movies)(Parameter: page)
                <br><b>Series List</b>(Method: get_series)(Parameter: page)
                <br><b>Related Posts</b>(Method: get_related_post)(Parameter: post_id, type(channel, movie, series), cat_id, page)
                <br><b>Episodes List</b>(Method: get_episodes)(Parameter: series_id, season_id)
                <br><b>Movie list by Lang. ID</b>(Method: get_movies_by_lang_id)(Parameter: lang_id, page)
                <br><b>Movie list by Genre ID</b>(Method: get_movies_by_gen_id)(Parameter: genre_id, page) (1,2,3)
                <br><b>Channels list by Cat ID</b>(Method: get_channels_by_cat_id)(Parameter:cat_id, page)
                <br><b>Single Series</b>(Method: get_single_series)(Parameter: series_id)
                <br><b>Single Movie</b>(Method: get_single_movie)(Parameter: movie_id)
                <br><b>Single Channels</b>(Method: get_single_channel)(Parameter: channel_id)
                <br><b>Search All</b>(Method: search_all)(Parameter:search_text)
                <br><b>Search Series</b>(Method: get_search_series)(Parameter:search_text, page)
                <br><b>Search Movies</b>(Method: get_search_movies)(Parameter:search_text, page)
                <br><b>Search Channels</b>(Method: get_search_channels)(Parameter:search_text, page)
                <br><b>User Report</b>(Method: user_report)(Parameter: user_id, post_id, report, type(channel, movie, series))
                <br><b>User Comments</b>(Method: user_comment)(Parameter: post_id, user_id, comment_text, type(channel, movie, series), is_limit(true, false))
                <br><b>Get User Comments</b>(Method: get_user_comment)(Parameter: post_id, type(channel, movie, series))
                <br><b>User's Rating</b>(Method: my_rating)(Parameter: post_id, user_id, type(channel, movie, series))
                <br><b>User Rating</b>(Method: user_rating)(Parameter: post_id, user_id, rate, type(channel, movie, series))
                <br><b>User Register</b>(Method: user_register)(Parameter:name,email,password,phone)
                <br><b>User Login</b>(Method: user_login)(Parameter:email,password)
                <br><b>User Profile</b>(Method: user_profile)(Parameter:user_id)
                <br><b>User Profile Update</b>(Method: user_profile_update)(Parameter:user_id,name,email,password,phone)
                <br><b>Forgot Password</b>(Method: forgot_pass)(Parameter:user_email)
                <br><b>App Details</b>(Method: get_app_details)(Parameter: user_id)
               </code> 
             </pre>
       		
       				</div>
          	</div>
        </div>
</div>
    <br/>
    <div class="clearfix"></div>
        
<?php include("includes/footer.php");?>       
