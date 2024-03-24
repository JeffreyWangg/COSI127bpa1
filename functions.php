<?php
    // SQL CONNECTIONS
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "COSI127b";

    // We will use PDO to connect to MySQL DB. This part need not be 
    // replicated if we are having multiple queries. 
    // initialize connection and set attributes for errors/exceptions
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // generic table builder. It will automatically build table data rows irrespective of result
    class TableRows extends RecursiveIteratorIterator {
        function __construct($it) {
            parent::__construct($it, self::LEAVES_ONLY);
        }

        function current() {
            return "<td style='text-align:center'>" . parent::current(). "</td>";
        }

        function beginChildren() {
            echo "<tr>";
        }

        function endChildren() {
            echo "</tr>" . "\n";
        }
    }

    function getAvailableMovieId($conn){
        $q = query($conn, "SELECT max(mpid) FROM Movie");
        $result = $q->fetch();
        return $result[0] + 1;
    }

    function getAvailableSeriesId($conn){
        $q = query($conn, "SELECT max(mpid) FROM Series");
        $result = $q->fetch();
        return $result[0] + 1;
    }

    //1
    function getAll($conn){
        $tableNames = ["MotionPicture", "Movie", "Series", "People", "Role", "Likes", "Location", "Genre", "Award", "guests", "User"];

        foreach ($tableNames as &$name){
            $stmt = query($conn, "SELECT * FROM {$name}");
            $result = $stmt->setFetchMode(PDO::FETCH_ASSOC);

            echo "<h1>{$name}</h1>";
            echo "<table class='table table-md table-bordered'>";
            echo "<thead class='thead-dark' style='text-align: center'>";
            // for each row that we fetched, use the iterator to build a table row on front-end
            foreach(new TableRows(new RecursiveArrayIterator($stmt->fetchAll())) as $k=>$v) {
                echo $v;
            }
            echo "</table>";
        }
    }

    function addMovie($conn, $movie_id, $name, $prod, $budget, $rating, $boxcol){
        query($conn, "INSERT INTO MotionPicture(id, name, rating, production, budget) VALUES ($movie_id, '$name', $rating, '$prod', $budget)");
        query($conn, "INSERT INTO Movie(mpid, boxoffice_collection) VALUES ($movie_id, $boxcol)");
    }

    function addSeries($conn, $series_id, $name, $prod, $budget, $rating, $seasons){
        query($conn, "INSERT INTO MotionPicture(id, name, rating, production, budget) VALUES ($series_id, '$name', $rating, '$prod', $budget)");
        query($conn, "INSERT INTO Movie(mpid, season_count) VALUES ($series_id, $seasons)");
    }

    //2
    function queryMotionPicture($conn, $mpName){
        $stmt = query($conn, "SELECT name, rating, production, budget FROM MotionPicture WHERE name = '$mpName'");
        $result = $stmt->setFetchMode(PDO::FETCH_ASSOC);
        return $stmt;
    }

    //3
    function likedMoviesByEmail($conn, $umail){
        $stmt = query($conn, "SELECT name, rating, production, budget FROM MotionPicture mp JOIN Likes l ON mp.id=l.mpid WHERE mp.id>=200 AND l.umail='$umail'");
        $result = $stmt->setFetchMode(PDO::FETCH_ASSOC);
        return $stmt;
    }

    //4
    function queryByCountry($conn, $country){
        $stmt = query($conn, "SELECT DISTINCT name FROM MotionPicture mp JOIN Location l ON mp.id=l.mpid AND country='$country'");
        $result = $stmt->setFetchMode(PDO::FETCH_ASSOC);
        return $stmt;
    }

    //5
    function directorsByZip($conn, $zip){
        $stmt = query($conn, "SELECT p.name AS pname, mp.name AS mpname FROM ((Location l JOIN Role r ON l.mpid=r.mpid AND l.zip='$zip' AND r.role_name='Director') JOIN People p ON p.id=r.pid)
                JOIN Series s ON s.mpid=l.mpid JOIN MotionPicture mp ON mp.id=l.mpid");
        $result = $stmt->setFetchMode(PDO::FETCH_ASSOC);
        return $stmt;
    }

    //8
    function prodByBC($conn, $bcmin, $bcmax){
        $stmt = query($conn, "SELECT DISTINCT production, name, boxoffice_collection, budget FROM MotionPicture JOIN Movie ON (mpid=id AND boxoffice_collection<=$bcmax AND boxoffice_collection>=$bcmin)");
        $result = $stmt->setFetchMode(PDO::FETCH_ASSOC);
        return $stmt;
    }

    //10
    function bostonThriller($conn){
        $stmt = query($conn, "SELECT DISTINCT mp.name, mp.rating FROM MotionPicture mp JOIN (Movie m JOIN Location l ON l.city='Boston' AND
                l.mpid NOT IN (SELECT m.mpid FROM Movie m JOIN Location l ON l.city!='Boston' AND l.mpid=m.mpid) AND m.mpid=l.mpid)
                ON mp.id=m.mpid");
        $result = $stmt->setFetchMode(PDO::FETCH_ASSOC);
        return $stmt;
    }

    //11
    function moviesByLikesAndAge($conn, $likesmin, $maxage){
        //grouped by umail 
        $stmt = query($conn, "SELECT mp.name, COUNT(l.umail) FROM MotionPicture mp JOIN Movie m ON m.mpid=mp.id 
                JOIN LIKES l ON m.mpid=l.mpid AND l.umail IN (SELECT email FROM User WHERE age<$maxage)
                GROUP BY mp.name HAVING COUNT(l.umail)>'$likesmin'");
        $result = $stmt->setFetchMode(PDO::FETCH_ASSOC);
        return $stmt;
    }

    //6
    function peopleByNumAwards($conn, $num){
        $stmt = query($conn, "SELECT DISTINCT p.name AS pname, mp.name AS mpname, award_year, award_count FROM (SELECT pid, mpid, award_year, COUNT(*) as award_count FROM Award GROUP BY award_year, mpid, pid HAVING COUNT(*)>=$num) q1 JOIN MotionPicture mp ON q1.mpid=mp.id JOIN People p ON p.id=q1.pid;");
        $result = $stmt->setFetchMode(PDO::FETCH_ASSOC);
        return $stmt;
    }

    //9
    function peopleByRolesAndRating($conn, $rating){
        $stmt = query($conn, "SELECT p.name, mp.name, role_count FROM (SELECT pid, mpid, COUNT(*) as role_count FROM Role GROUP BY mpid, pid HAVING COUNT(*)>1) q1 JOIN People p ON p.id=q1.pid JOIN MotionPicture mp ON mp.id=q1.mpid AND mp.rating>$rating;");
        $result = $stmt->setFetchMode(PDO::FETCH_ASSOC);
        return $stmt;
    }

    //7
    function youngOldAwardWinners($conn){
        $stmt = query($conn, "SELECT name, id, dob FROM People WHERE dob IN ((SELECT MIN(dob) FROM (People JOIN Award ON pid=id)), (SELECT MAX(dob) FROM (People JOIN Award ON pid=id)))");
        $result = $stmt->setFetchMode(PDO::FETCH_ASSOC);
        return $stmt;
    }

    //12
    function actorsMarvelAndWarner($conn){  
        $stmt = query($conn, "SELECT p.name FROM People p JOIN Role r ON p.id=r.pid AND r.role_name='Actor' 
                JOIN MotionPicture mp ON mp.id=r.mpid WHERE mp.production IN ('Marvel', 'Warner Bros') GROUP BY p.name HAVING COUNT(*)=2");
        $result = $stmt->setFetchMode(PDO::FETCH_ASSOC);
        return $stmt;
    }

    //13
    function betterThanComedy($conn){  
        $stmt = query($conn, "SELECT name, rating FROM MotionPicture WHERE rating>(SELECT AVG(rating) FROM MotionPicture JOIN Genre ON genre_name='Comedy' AND id=mpid)");
        $result = $stmt->setFetchMode(PDO::FETCH_ASSOC);
        return $stmt;
    }

    //14
    function topWithMostPeople($conn){  
        $stmt = query($conn, "SELECT mp.name, COUNT(DISTINCT p.name), COUNT(DISTINCT r.role_name) FROM People p JOIN Role r ON p.id=r.pid JOIN MotionPicture mp ON r.mpid=mp.id
                GROUP BY r.mpid ORDER BY COUNT(DISTINCT p.name, r.mpid) DESC LIMIT 5");
        $result = $stmt->setFetchMode(PDO::FETCH_ASSOC);
        return $stmt;
    }

    //15
    function actorsSharedBirth($conn){  
        $stmt = query($conn, "SELECT p1.name AS name1, p2.name AS name2, p1.dob FROM People p1 INNER JOIN People p2 ON p1.id!=p2.id AND p1.dob=p2.dob WHERE p1.name<p2.name");
        $result = $stmt->setFetchMode(PDO::FETCH_ASSOC);
        return $stmt;
    }


    function query($conn, $sql){
        try{
            $stmt = $conn->prepare("{$sql}");
            $stmt->execute();
            return $stmt;
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
?>

