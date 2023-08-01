<footer class="app-footer">
      <div class="row">
        <div class="col-xs-12">
          <div class="footer-copyright">Copyright © <?php echo date('Y');?> <a href="https://produto.mercadolivre.com.br/MLB-1172719775-script-aplicativo-video-stream-painel-php-_JM?quantity=1" target="_blank">meusite.com</a>. All Rights Reserved.</div>
        </div>
      </div>
    </footer>
  </div>
</div>
<script type="text/javascript" src="assets/js/vendor.js"></script> 
<script type="text/javascript" src="assets/js/app.js"></script>

<script type="text/javascript">
	$(".alert").delay(2000).fadeOut(200, function() {
	      $(this).alert('close');
	  });
</script>

<script type="text/javascript">

  if($(".dropdown-li").hasClass("active")){
    var _test='<?php echo $active_page; ?>';
    $("."+_test).next(".cust-dropdown-container").slideDown();
    $("."+_test).find(".title").next("i").removeClass("fa-angle-right",1000);
    $("."+_test).find(".title").next("i").addClass("fa-angle-down",1000);
  }

  $(document).ready(function(e){
    var _flag=false;

    

    $(".dropdown-a").click(function(e){
        
      $(this).parents("ul").find(".cust-dropdown-container").slideUp();

      $(this).parents("ul").find(".title").next("i").addClass("fa-angle-right");
      $(this).parents("ul").find(".title").next("i").removeClass("fa-angle-down");

      if($(this).parent("li").next(".cust-dropdown-container").css('display') !='none'){
          $(this).parent("li").next(".cust-dropdown-container").slideUp();
          $(this).find(".title").next("i").addClass("fa-angle-right",1000);
          $(this).find(".title").next("i").removeClass("fa-angle-down",1000);
      }else{
        $(this).parent("li").next(".cust-dropdown-container").slideDown();
        $(this).find(".title").next("i").removeClass("fa-angle-right",1000);
        $(this).find(".title").next("i").addClass("fa-angle-down",1000);
      }

    });
  });
</script>

</body>
</html>