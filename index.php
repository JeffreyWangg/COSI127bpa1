<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

    <!-- Bootstrap JS dependencies -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COSI 127b</title>
</head>
<body>
    <?php
        include 'functions.php';
    ?>
    <div class="container">
        <h1 style="text-align:center">COSI 127b</h1><br>
        <h3 style="text-align:center">Connecting Front-End to MySQL DB</h3><br>
    </div>

    <div class="container">
        <form method="get" action="./alltables.php">
            <button style="margin-left:45%" class="btn btn-outline-secondary" type="submit" name="allTables" id="button-addon2">View All Tables</button>
        </form>
    </div>
    <div class="container">
        <form method="get" action="./youngoldaward.php">
            <button style="margin-left:30%" class="btn btn-outline-secondary" type="submit" name="youngOldAward" id="button-addon2">View Youngest and Oldest Actors With At Least One Award</button>
        </form>
    </div>

    <!-- add movie form  -->
    <div class="container">
        <h2>Add Motion Picture</h2>
        <form id="mpAddForm" method="post" action="index.php">
            <div class="input-group mb-3">
                <select name="inputType" id="inputType" required>
                    <option value="" selected>Select An Option</option>
                    <option value="Movie">Movie</option>
                    <option value="Series">Series</option>
                </select>
                <input type="text" class="form-control" placeholder="Enter Name" name="inputName" id="inputName" required>
                <input type="text" class="form-control" placeholder="Enter Production" name="inputProduction" id="inputProduction" required>
                <input type="number" min="0" max="10" step="0.1" class="form-control" placeholder="Enter Rating" name="inputRating" id="inputRating" required>
                <input type="number" min="0" max="99999999999999999999" class="form-control" placeholder="Enter Budget" name="inputBudget" id="inputBudget" required>
                <div class="input-group-append">
                    <button class="btn btn-outline-secondary" type="submit" name="addMotionPicture" id="button-addon2">Add</button>
                </div>
            </div>
        </form>
    </div>

    <?php
    if(isset($_POST['addMotionPicture']))
        {
            $type = $_POST["inputType"]; 
            $name = $_POST["inputName"]; 
            $production = $_POST["inputProduction"]; 
            $rating = $_POST["inputRating"]; 
            $budget = $_POST["inputBudget"]; 
            $boxcol = $_POST["inputBoxCol"]; 
            $seasons = $_POST["inputSeasons"]; 

            if(strcmp($type, "Movie") == 0){
                echo "movie adding";
                addMovie($conn, getAvailableMovieId($conn), $name, $production, $rating, $budget, $boxcol);
            } else if(strcmp($type, "Series") == 0){
                echo "series adding";
                addSeries($conn, getAvailableSeriesId($conn), $name, $production, $rating, $budget, $seasons);
            } else {
                echo "none";
            }
        }
    ?>

    <!-- query  motion piture-->
    <div class="container">
    <h1>Query Motion Picture By Name</h1>
        <form id="MPQueryForm" method="post" action="index.php">
            <div class="input-group mb-3">
                <input type="text" class="form-control" placeholder="Enter Motion Picture name" name="inputMPName" id="inputMPName">
                <div class="input-group-append">
                    <button class="btn btn-outline-secondary" type="submit" name="queryMP" id="button-addon2">Query</button>
                </div>
            </div>
        </form>

    <?php
        if(isset($_POST['queryMP'])){
            $mpName = $_POST['inputMPName'];
            $stmt = queryMotionPicture($conn, $mpName);
            echo "<h2>Results for: {$mpName}</h2>";
            echo "<table class='table table-md table-bordered'>";
            echo "<thead class='thead-dark' style='text-align: center'>";
            echo "<tr><th class='col-md-2'>Name</th><th class='col-md-2'>Rating</th><th class='col-md-2'>Production</th><th class='col-md-2'>Budget</th></tr></thead>";
            // for each row that we fetched, use the iterator to build a table row on front-end
            foreach(new TableRows(new RecursiveArrayIterator($stmt->fetchAll())) as $k=>$v) {
                echo $v;
            }
            echo "</table>";
        }
    ?>
    </div>

    <div class="container">
    <h1>Query Movies By User's Likes</h1>
        <form id="MPQueryForm" method="post" action="index.php">
            <div class="input-group mb-3">
                <input type="text" class="form-control" placeholder="Enter User Email" name="inputUmail" id="inputUmail">
                <div class="input-group-append">
                    <button class="btn btn-outline-secondary" type="submit" name="queryByUmail" id="button-addon2">Query</button>
                </div>
            </div>
        </form>

    <?php
        if(isset($_POST['queryByUmail'])){
            $umail = $_POST['inputUmail'];
            $stmt = likedMoviesByEmail($conn, $umail);
            echo "<h2>Results for: {$umail}</h2>";
            echo "<table class='table table-md table-bordered'>";
            echo "<thead class='thead-dark' style='text-align: center'>";
            // for each row that we fetched, use the iterator to build a table row on front-end
            echo "<tr><th class='col-md-2'>Name</th><th class='col-md-2'>Rating</th><th class='col-md-2'>Production</th><th class='col-md-2'>Budget</th></tr></thead>";
            foreach(new TableRows(new RecursiveArrayIterator($stmt->fetchAll())) as $k=>$v) {
                echo $v;
            }
            echo "</table>";
        }
    ?>
    </div>

    <div class="container">
    <h1>Query Motion Pictures By Country</h1>
        <form id="countryQueryForm" method="post" action="index.php">
            <div class="input-group mb-3">
                <input type="text" class="form-control" placeholder="Enter Country" name="inputCountry" id="inputCountry">
                <div class="input-group-append">
                    <button class="btn btn-outline-secondary" type="submit" name="queryByCountry" id="button-addon2">Query</button>
                </div>
            </div>
        </form>

    <?php
        if(isset($_POST['queryByCountry'])){
            $country = $_POST['inputCountry'];
            $stmt = queryByCountry($conn, $country);
            echo "<h2>Results for: {$country}</h2>";
            echo "<table class='table table-md table-bordered'>";
            echo "<thead class='thead-dark' style='text-align: center'>";
            // for each row that we fetched, use the iterator to build a table row on front-end
            echo "<tr><th class='col-md-2'>Name</th><th class='col-md-2'>Rating</th><th class='col-md-2'>Production</th><th class='col-md-2'>Budget</th></tr></thead>";
            echo "<tr><th class='col-md-2'>Name</th></tr></thead>";
            foreach(new TableRows(new RecursiveArrayIterator($stmt->fetchAll())) as $k=>$v) {
                echo $v;
            }
            echo "</table>";
        }
    ?>
    </div>

    <div class="container">
    <h1>Query Directors By Zip Code</h1>
        <form id="directorQueryForm" method="post" action="index.php">
            <div class="input-group mb-3">
                <input type="number" class="form-control" placeholder="Enter Zip Code" name="inputZip" id="inputZip">
                <div class="input-group-append">
                    <button class="btn btn-outline-secondary" type="submit" name="queryByZip" id="button-addon2">Query</button>
                </div>
            </div>
        </form>

    <?php
        if(isset($_POST['queryByZip'])){
            $zip = $_POST['inputZip'];
            $stmt = directorsByZip($conn, $zip);
            echo "<h2>Results for: {$zip}</h2>";
            echo "<table class='table table-md table-bordered'>";
            echo "<thead class='thead-dark' style='text-align: center'>";
            echo "<tr><th class='col-md-2'>Director Name</th><th class='col-md-2'>Show Name</th></tr></thead>";
            // for each row that we fetched, use the iterator to build a table row on front-end
            foreach(new TableRows(new RecursiveArrayIterator($stmt->fetchAll())) as $k=>$v) {
                echo $v;
            }
            echo "</table>";
        }
    ?>
    </div>

    <div class="container">
    <h1>Query People By Awards Won In a Single Motion Picture In a Single Year</h1>
        <form id="awardNumQueryForm" method="post" action="index.php">
            <div class="input-group mb-3">
                <input type="number" class="form-control" placeholder="Enter Number of Awards" name="inputNumAward" id="inputNumAward">
                <div class="input-group-append">
                    <button class="btn btn-outline-secondary" type="submit" name="queryByNumAward" id="button-addon2">Query</button>
                </div>
            </div>
        </form>

    <?php
        if(isset($_POST['queryByNumAward'])){
            $num = $_POST['inputNumAward'];
            $stmt = peopleByNumAwards($conn, $num);
            echo "<h2>Results for: {$num}</h2>";
            echo "<table class='table table-md table-bordered'>";
            echo "<thead class='thead-dark' style='text-align: center'>";
            echo "<tr><th class='col-md-2'>Actor Name</th><th class='col-md-2'>Movie/Show Name</th><th class='col-md-2'>Year Won</th><th class='col-md-2'>Number Awards Won</th></tr></thead>";
            // for each row that we fetched, use the iterator to build a table row on front-end
            foreach(new TableRows(new RecursiveArrayIterator($stmt->fetchAll())) as $k=>$v) {
                echo $v;
            }
            echo "</table>";
        }
    ?>
    </div>

    <div class="container">
    <h1>Query Producers By Box Office Min/Max</h1>
        <form id="queryProdByBC" method="post" action="index.php">
            <div class="input-group mb-3">
                <input type="number" class="form-control" placeholder="Enter Box Office Minimum" name="inputBCMin" id="inputBCMin">
                <input type="number" class="form-control" placeholder="Enter Box Office Maximum" name="inputBCMax" id="inputBCMax">
                <div class="input-group-append">
                    <button class="btn btn-outline-secondary" type="submit" name="queryByBC" id="button-addon2">Query</button>
                </div>
            </div>
        </form>

    <?php
        if(isset($_POST['queryByBC'])){
            $bcmin = $_POST['inputBCMin'];
            $bcmax = $_POST['inputBCMax'];
            $stmt = prodByBC($conn, $bcmin, $bcmax);
            echo "<h2>Results for Min:{$bcmin} Max:{$bcmax} </h2>";
            echo "<table class='table table-md table-bordered'>";
            echo "<thead class='thead-dark' style='text-align: center'>";
            echo "<tr><th class='col-md-2'>Production</th><th class='col-md-2'>Movie Name</th><th class='col-md-2'>Box Office Collection</th><th class='col-md-2'>Budget</th></tr></thead>";
            // for each row that we fetched, use the iterator to build a table row on front-end
            foreach(new TableRows(new RecursiveArrayIterator($stmt->fetchAll())) as $k=>$v) {
                echo $v;
            }
            echo "</table>";
        }
    ?>
    </div>

    <div class="container">
    <h1>TOP 2 THRILLERS FROM BOSTON</h1>
    <?php
        $stmt = bostonThriller($conn);
        echo "<table class='table table-md table-bordered'>";
        echo "<thead class='thead-dark' style='text-align: center'>";
        // for each row that we fetched, use the iterator to build a table row on front-end
        echo "<tr><th class='col-md-2'>Name</th><th class='col-md-2'>Rating</th></tr></thead>";
        foreach(new TableRows(new RecursiveArrayIterator($stmt->fetchAll())) as $k=>$v) {
            echo $v;
        }
        echo "</table>";
    ?>
    </div>

    <div class="container">
    <h1>Query Movies By Likes and Age</h1>
        <form id="queryMovieByLikesAge" method="post" action="index.php">
            <div class="input-group mb-3">
                <input type="number" class="form-control" placeholder="Enter Minimum Likes" name="inputLikesMin" id="inputLikesMin">
                <input type="number" class="form-control" placeholder="Enter Maximum Age" name="inputAgeMax" id="inputAgeMax">
                <div class="input-group-append">
                    <button class="btn btn-outline-secondary" type="submit" name="queryByLikesAge" id="button-addon2">Query</button>
                </div>
            </div>
        </form>

    <?php
        if(isset($_POST['queryByLikesAge'])){
            $minlikes = $_POST['inputLikesMin'];
            $maxage = $_POST['inputAgeMax'];
            $stmt = moviesByLikesAndAge($conn, $minlikes, $maxage);
            echo "<h2>Results for Min Likes:{$minlikes} Max Age:{$maxage} </h2>";
            echo "<table class='table table-md table-bordered'>";
            echo "<thead class='thead-dark' style='text-align: center'>";
            echo "<tr><th class='col-md-2'>Name</th><th class='col-md-2'>Number of Likes</th></tr></thead>";
            // for each row that we fetched, use the iterator to build a table row on front-end
            foreach(new TableRows(new RecursiveArrayIterator($stmt->fetchAll())) as $k=>$v) {
                echo $v;
            }
            echo "</table>";
        }
    ?>
    </div>

    <div class="container">
    <h1>Query Motion Pictures With Actors Who Have More Than One Role By Rating </h1>
        <form id="queryMPByRating" method="post" action="index.php">
            <div class="input-group mb-3">
                <input type="number" class="form-control" placeholder="Enter Minimum Rating" name="inputRatingMin" id="inputRatingMin">
                <div class="input-group-append">
                    <button class="btn btn-outline-secondary" type="submit" name="queryByRatingMin" id="button-addon2">Query</button>
                </div>
            </div>
        </form>

    <?php
        if(isset($_POST['queryByRatingMin'])){
            $rating = $_POST['inputRatingMin'];
            $stmt = peopleByRolesAndRating($conn, $rating);
            echo "<h2>Results for Minimum Rating:{$rating}";
            echo "<table class='table table-md table-bordered'>";
            echo "<thead class='thead-dark' style='text-align: center'>";
            echo "<tr><th class='col-md-2'>Name</th><th class='col-md-2'>Motion Picture Name</th><th class='col-md-2'>Number Of Roles</th></tr></thead>";
            // for each row that we fetched, use the iterator to build a table row on front-end
            foreach(new TableRows(new RecursiveArrayIterator($stmt->fetchAll())) as $k=>$v) {
                echo $v;
            }
            echo "</table>";
        }
    ?>
    </div>

    <div class="container">
    <h1>ACTORS PART OF WARNER BROS AND MARVEL PRODUCTIONS</h1>
    <?php
        $stmt = actorsMarvelAndWarner($conn);
        echo "<table class='table table-md table-bordered'>";
        echo "<thead class='thead-dark' style='text-align: center'>";
        echo "<tr><th class='col-md-2'>Actor Name</th><th class='col-md-2'>Motion Picture Name</th></tr></thead>";
        // for each row that we fetched, use the iterator to build a table row on front-end
        foreach(new TableRows(new RecursiveArrayIterator($stmt->fetchAll())) as $k=>$v) {
            echo $v;
        }
        echo "</table>";
    ?>
    </div>

    <div class="container">
    <h1>PICTURES BETTER THAN ALL COMEDY PICTURES AVERAGE</h1>
    <?php
        $stmt = betterThanComedy($conn);
        echo "<table class='table table-md table-bordered'>";
        echo "<thead class='thead-dark' style='text-align: center'>";
        echo "<tr><th class='col-md-2'>Name</th><th class='col-md-2'>Rating</th></tr></thead>";
        // for each row that we fetched, use the iterator to build a table row on front-end
        foreach(new TableRows(new RecursiveArrayIterator($stmt->fetchAll())) as $k=>$v) {
            echo $v;
        }
        echo "</table>";
    ?>
    </div>

    <div class="container">
    <h1>TOP 5 MOVIES WITH MOST PEOPLE</h1>
    <?php
        $stmt = topWithMostPeople($conn);
        echo "<table class='table table-md table-bordered'>";
        echo "<thead class='thead-dark' style='text-align: center'>";
        echo "<tr><th class='col-md-2'>Name</th><th class='col-md-2'>People COunt</th><th class='col-md-2'>Role Count</th></tr></thead>";
        // for each row that we fetched, use the iterator to build a table row on front-end
        foreach(new TableRows(new RecursiveArrayIterator($stmt->fetchAll())) as $k=>$v) {
            echo $v;
        }
        echo "</table>";
    ?>
    </div>

    <div class="container">
    <h1>ACTORS WITH SAME BIRTHDAY</h1>
    <?php
        $stmt = actorsSharedBirth($conn);
        echo "<table class='table table-md table-bordered'>";
        echo "<thead class='thead-dark' style='text-align: center'>";
        echo "<tr><th class='col-md-2'>Actor 1</th><th class='col-md-2'>Actor 2</th><th class='col-md-2'>Date of Birth</th></tr></thead>";
        // for each row that we fetched, use the iterator to build a table row on front-end
        foreach(new TableRows(new RecursiveArrayIterator($stmt->fetchAll())) as $k=>$v) {
            echo $v;
        }
        echo "</table>";
    ?>
    </div>
    
    </body>
    <script type="text/javascript">
        let inputType = document.getElementById("inputType");
        let form = document.getElementById("mpAddForm");
        inputType.addEventListener("change", ()=>{
            console.log(form.elements[5].nodeName)
            if(form.elements[5].nodeName == "INPUT"){
                form.elements[5].remove();
            }
            console.log(form.elements[4])
            if(inputType.value == "Movie"){
                form.elements[4].insertAdjacentHTML('afterend','<input type="number" min="0" max="99999999999999999999" class="form-control" placeholder="Enter Box Office Collection" name="inputBoxCol" id="inputBoxCol" required>');
            } else if(inputType.value == "Series"){
                form.elements[4].insertAdjacentHTML('afterend','<input type="number" min="0" max="100" class="form-control" placeholder="Enter Season Count" name="inputSeasons" id="inputSeasons" required>');
            }
        })
    </script>
</html>
