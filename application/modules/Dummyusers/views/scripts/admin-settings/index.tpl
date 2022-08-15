<h1>Dummy Users</h1>
<p>Create dummy users with profiles. If enabled, 1000 dummy users will be created with profiles. If disabled, the dummy users will be removed. Changing this setting will effect more than 10.000 rows in the database. It's advisable to make a back up of your database before proceeding.</p>


<?php echo $this->form->render(); ?>

<script>
    document.querySelector("form").addEventListener("submit", function(e){
        e.preventDefault();
        var answer = confirm("Did you create a backup of your database?");
        if(answer) e.currentTarget.submit();
    })
</script>