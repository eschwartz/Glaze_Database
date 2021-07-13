<?php
// Creates JS Assoc Array "ingrArry"
// Creates PHP $postVars array on non-ingredient variables
include('includes/DYLMG_postGlaze_passonvars.php');
?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Do you like my glaze</title>
<link href="stylesheets/sandbox_doyoulike.css" rel="stylesheet" type="text/css" />
</head>

<body>
<div class="master-frame">
    <div class="header"><img src="images/DYLMG_title.png" style="width:35%" /></div>
    <div class="sidebar">
        <?php
        include('includes/sidebar.php');
        ?>
    </div>
    <div class="content-border">
<!-- __________________________________________ Content Start Here_______________________________________-->


<?php
// quickQuery($sql) return query resutls in a table
include('includes/quickQuery.php');
$sql = "SELECT Glazes.GlazeName, Ingredients.IngredientName\n"
    . "FROM (GlazeIngredients INNER JOIN Glazes ON GlazeIngredients.GlazeID = Glazes.GlazeID) \n"
    . "INNER JOIN Ingredients ON GlazeIngredients.IngredientID = Ingredients.IngredientID LIMIT 0, 30 ";
	


?>

<!-- __________________________________________ Content Ends Here_______________________________________________-->
    </div>
</div>
</body>
</html>