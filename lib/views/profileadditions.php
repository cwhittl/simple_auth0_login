<?php ?>
<table class="form-table">
<tbody><tr>
    <th><label for="description">Password</label></th>
    <td><a id="password_reset" href="#">Need a new password? Click here</a></td>
</tr>
</tbody></table>

<script>
var password_reset = document.getElementById("password_reset")
password_reset.addEventListener('click', function(e) {
  e.preventDefault();
    simpleAuth0LoginShared.resetPassword("<?php echo $email; ?>").then(function(data){
      if(data.hasOwnProperty("success") && (data.success === "true" || data.success === true )){
        alert("A password request has been sent to your email.");
      }else{
        console.log(data);
        alert("There was an unknown error, please contact <?php echo $support_email; ?> if this continues");
      }
    },function(data){
      console.log(data);
      alert("There was an unknown error, please contact <?php echo $support_email; ?> if this continues");
    });
});
</script>
