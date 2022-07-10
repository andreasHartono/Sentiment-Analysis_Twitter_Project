<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Cache-Control" content="no-cache">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Lang" content="en">
<title>Home</title>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
<style type="text/css">
    body {
        background-color: #F0F8FF;
        text-align: center;
        background-image: url("http://assets.stickpng.com/images/580b57fcd9996e24bc43c53e.png");
        background-position: right bottom;
        background-repeat: no-repeat;
        background-size: 150px 150px;
        background-attachment: fixed;
    }
    h1 {
        font-family: fantasy;
        padding-top: 25px;
    }
    label {
        font-family: Arial, Helvetica, sans-serif;
    }
    #search {
        margin-left: 10px;
        padding-right: 20px;
        padding-left: 20px;
        background-color: #1E90FF;
        border: none;
    }
    tr,td {
        text-align: left;
        padding-right: 10px;
    }
    .bar {
      overflow: hidden;
      background-color: #ADD8E6;
    }

    .bar a {
      float: left;
      color: black;
      text-align: center;
      padding: 14px 16px;
      text-decoration: none;
      font-size: 17px;
    }

    .bar a:hover {
      background-color: #E0FFFF;
      color: black;
    }

    .bar a.active {
      background-color: #48D1CC;
      color: black;
      font-weight: bold;
    }
    #container {
        margin: auto;
        align-content: center;
        justify-content: center;
        display: flex;
    }
</style>
</head>
<body>
<div class="bar">
    <a class="active" href="home.php">Home</a>
    <a href="evaluasi.php">Evaluation</a>
</div>
<h1>Crawling Tweet and Sentiment Analysis</h1>
<form method="GET" action="">
    <label><b>Keyword &nbsp;</b></label> 
    <input type="text" name="q" placeholder="Insert Keyword" />
    <input type="submit" id="search" value="Search" name="search" />
    <br><br>
    <label>
        <table>
            <tr>
                <td>
                    <label><b>Choose Similarity Method</b></label>
                </td>
                <td>
                    <label><input type='radio' name='similarity' value='Asymmetric' checked> Asymmetric</label><br>
                    <label><input type='radio' name='similarity' value='Jaccard'> Jaccard</label><br>
                    <label><input type='radio' name='similarity' value='Overlap'> Overlap</label>
                </td>
            </tr>
        </table>
    </label>
</form>

<?php
    require("train.php");
    $koneksi = new mysqli("localhost", "root", "", "project_iir");

    if ($koneksi->connect_errno) {
        die("Failed to connect to MySQL: " . $koneksi->connect_error);
    }

    $count0 = 0;
    $count1 = 0;
    $count05 = 0;

    if (isset($_GET['q']) && $_GET['q'] != '') {
        include_once(dirname(__FILE__) . '/config.php');
        include_once(dirname(__FILE__) . '/lib/TwitterSentimentAnalysis.php');

        $TwitterSentimentAnalysis = new TwitterSentimentAnalysis(DATUMBOX_API_KEY, TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET, TWITTER_ACCESS_KEY, TWITTER_ACCESS_SECRET);

        //Search Tweets parameters as described at https://dev.twitter.com/docs/api/1.1/get/search/tweets
        // set parameter dari sentiment analysis
        $twitterSearchParams = array(
            'q' => $_GET['q'], //query
            'lang' => 'in', //bahasa yg digunakan
            'count' => 100, //berapa banyak crawling
        );

        $results = $TwitterSentimentAnalysis->sentimentAnalysis($twitterSearchParams); 

?>
        <br>
        <h5>Results for "<?php echo $_GET['q']; ?>"</h5><br>
        <table class="table table-bordered table-striped table-hovered table-bordered mx-auto
        " style="width: 1000px;">
            <tr>
                <td>User_id</td>
                <td>Tweet</td>
                <td>Sentiment</td>
            </tr>
<?php
            $effected_id = [];

            $sql1 = "DELETE FROM tweet WHERE tweet_id >= 1012";
            $hasil1 = $koneksi->query($sql1);

            foreach ($results as $tweet) {
                $sql = "INSERT INTO tweet(content,user_id) VALUES (?,?)";
                $hasil = $koneksi->prepare($sql);
                $hasil->bind_param("ss", $tweet['text'], $tweet['user']);
                $hasil->execute();
                $new_id = $koneksi->insert_id;

                array_push($effected_id, $new_id);
            }
            
            // do Sentiment
            $sentiment = new Sentiment;
            $hasil = $sentiment->sentiment($effected_id,$_GET['similarity']);
            $sql_show = "SELECT user_id, content, isPositive FROM tweet WHERE tweet_id >= $effected_id[0]";
            $hasil2 = $koneksi->query($sql_show);

            while ($baris = $hasil2->fetch_assoc()) { 
                if($baris['isPositive'] == "0"){
                    $count0++;
                } else if($baris['isPositive'] == "1"){
                    $count1++;
                } else if ($baris['isPositive'] == "0.5"){
                    $count05++;
                }

?>
                <tr>
                    <td>@<?php echo $baris['user_id']; ?></td>
                    <td><?php echo $baris['content']; ?></td>
                    <td>
                        <?php if($baris['isPositive'] == "0") echo "Negative"; 
                            else if($baris['isPositive'] == "1") echo "Positive"; 
                            else if($baris['isPositive'] == "0.5") echo "Netral";?>
                    </td>
                </tr>
<?php
    }

    $koneksi->close(); 
?>
        </table>
        <div id="container"></div>
<?php
    }
?>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

<script type="text/javascript">
// Load google charts
google.charts.load('current', {'packages':['corechart']});
google.charts.setOnLoadCallback(drawChart);

// Draw the chart and set the chart values
function drawChart() {
  var data = google.visualization.arrayToDataTable([
  ['Task', 'Hours per Day'],
  ['Negative', <?php echo $count0 ?>],
  ['Netral', <?php echo $count05 ?>],
  ['Positive', <?php echo $count1 ?>]
]);

  // Optional; add a title and set the width and height of the chart
  var options = {'title':'Pie Chart Sentiment', 'width':500, 'height':400};

  // Display the chart inside the <div> element with id="piechart"
  var chart = new google.visualization.PieChart(document.getElementById('container'));
  chart.draw(data, options);
}
</script>
</body>
</html>