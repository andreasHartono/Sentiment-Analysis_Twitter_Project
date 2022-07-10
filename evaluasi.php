<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Cache-Control" content="no-cache">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta http-equiv="Lang" content="en">
    <title>Evaluasi</title>
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

        tr,
        td {
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
    </style>
</head>

<body>
    <div class="bar">
        <a href="home.php">Home</a>
        <a class="active" href="evaluasi.php">Evaluation</a>
    </div>
    <h1>Evaluation Sentiment Analysis</h1>

    <?php
    // Imports
    require_once __DIR__ . '/vendor/autoload.php';

    use Phpml\FeatureExtraction\TokenCountVectorizer;
    use Phpml\Tokenization\WhitespaceTokenizer;
    use Phpml\FeatureExtraction\TfIdfTransformer;
    use Phpml\Math\Distance\Jaccard;
    use Phpml\Math\Distance\Asymmetric;
    use Phpml\Math\Distance\Overlap;
    use Phpml\Classification\KNearestNeighbors;
    use Phpml\CrossValidation\StratifiedRandomSplit;
    use Phpml\Dataset\ArrayDataset;
    use Phpml\Metric\Accuracy;

    $stemmerfactory = new \Sastrawi\Stemmer\StemmerFactory();
    $stemmer = $stemmerfactory->createStemmer();

    $stopwordFactory = new \Sastrawi\StopWordRemover\StopWordRemoverFactory();
    $stopword = $stopwordFactory->createStopWordRemover();

    // KONEKSI
    $koneksi = new mysqli("localhost", "root", "", "project_iir");

    // AMBIL JUMLAH DATA
    $sql = "SELECT * from tweet WHERE tweet_id<=1011";
    $hasil = $koneksi->query($sql);

    $datasets = array();
    $arr_labels = array();

    // Dataset
    while ($baris = $hasil->fetch_assoc()) {
        if ($baris['isPositive'] == 1) {
            $labels = 'Positif';
        } elseif ($baris['isPositive'] == 0.5) {
            $labels = 'Netral';
        } elseif ($baris['isPositive'] == 0) {
            $labels = 'Negatif';
        }

        // preprocess data
        $stemTweet = $stemmer->stem($baris['content']);
        $stopTweet = $stopword->remove($stemTweet);

        array_push($datasets, $stopTweet);
        array_push($arr_labels, $labels);
    }

    $dataset = new ArrayDataset(
        $samples = $datasets,
        $targets = $arr_labels
    );

    // Split data for train and test
    // train:test = 80:20
    $split = new StratifiedRandomSplit($dataset, 0.2);

    // train group
    $dataset_train = $split->getTrainSamples();
    $labels_train = $split->getTrainLabels();

    // test group
    $dataset_test = $split->getTestSamples();
    $labels_test = $split->getTestLabels();

    $arr_j = array();
    $arr_o = array();
    $arr_a = array();

    for ($i = 0; $i < count($dataset_test); $i++) {
        $dataset_train[808] = $dataset_test[$i];

        $test = $dataset_train;

        // TF-IDF
        $tf = new TokenCountVectorizer(new WhitespaceTokenizer());
        $tf->fit($test);
        $tf->transform($test);
        $vocabulary = $tf->getVocabulary();

        $tfidf = new TfIdfTransformer($test);
        $tfidf->transform($test);

        // SENTIMENT    
        $totalNew = count($vocabulary);
        $k_value = $totalNew / 3;

        // Jaccard
        $classifier1 = new KNearestNeighbors($k_value, new Jaccard());
        $classifier1->train(array_slice($test, 0, -1), $labels_train);
        $result1 = $classifier1->predict(array_slice($test, -1));

        array_push($arr_j, $result1);

        // Asymmetric
        $classifier2 = new KNearestNeighbors($k_value, new Asymmetric());
        $classifier2->train(array_slice($test, 0, -1), $labels_train);
        $result2 = $classifier2->predict(array_slice($test, -1));

        array_push($arr_a, $result2);

        // Overlap
        $classifier3 = new KNearestNeighbors($k_value, new Overlap());
        $classifier3->train(array_slice($test, 0, -1), $labels_train);
        $result3 = $classifier3->predict(array_slice($test, -1));

        array_push($arr_o, $result3);
    }

    ?>
    <div id="k1">
        <h3>KNN With Jaccard</h3>
        <table class="table table-bordered table-striped table-hovered table-bordered mx-auto
        " style="width: 250px;">
            <tr>
                <th>Tweets</th>
                <th>Sentiment Original</th>
                <th>Sentiment System</th>
                <th>Valid</th>
            </tr>

            <?php
            $arr_ori1 = array();
            $arr_knn1 = array();
            $valid = 0;
            $novalid = 0;

            for ($i = 0; $i < count($arr_j); $i++) {
                echo "<tr>";
                echo "<td>".$dataset_test[$i]."</td>";
                echo "<td>" . $labels_test[$i] . "</td>";
                echo "<td>" . $arr_j[$i][0] . "</td>";
                array_push($arr_knn1,$arr_j[$i][0]);

                if ($labels_test[$i] == $arr_j[$i][0]) {
                    echo "<td>V</td>";
                    $valid++;
                } else {
                    echo "<td>X</td>";
                    $novalid++;
                }
                echo "</tr>";
            }
            $accuracy = 100 * (Accuracy::score($labels_test, $arr_knn1));
            ?>
        </table>
        <p>Jumlah Data Testing: <?php echo count($dataset_test); ?></p>
        <p>Jumlah Valid: <?php echo $valid; ?></p>
        <p>Akurasi: <?php echo $accuracy . "%"; ?></p>
    </div>

    <div id="k2">
        <h3>KNN With Asymmetric</h3>
        <table class="table table-bordered table-striped table-hovered table-bordered mx-auto
        " style="width: 250px;">
        <tr>
            <th>Tweets</th>
            <th>Sentiment Original</th>
            <th>Sentiment System</th>
            <th>Valid</th>
        </tr>
    <?php
    $arr_ori2 = array();
    $arr_knn2 = array();
    $valid2 = 0;
    $novalid2 = 0;

    for ($i = 0; $i < count($arr_a); $i++) {
        echo "<tr>";
        echo "<td>".$dataset_test[$i]."</td>";
        echo "<td>" . $labels_test[$i] . "</td>";
        echo "<td>" . $arr_a[$i][0] . "</td>";
        array_push($arr_knn2,$arr_a[$i][0]);
        if ($labels_test[$i] == $arr_a[$i][0]) {
            echo "<td>V</td>";
            $valid2++;
        } else {
            echo "<td>X</td>";
            $novalid2++;
        }
        echo "</tr>";
    }
    $accuracy2 = 100 * (Accuracy::score($labels_test, $arr_knn2));

    ?>
    </table>
    <p>Jumlah Data Testing: <?php echo count($dataset_test); ?></p>
    <p>Jumlah Valid: <?php echo $valid2; ?></p>
    <p>Akurasi: <?php echo $accuracy2 . "%"; ?></p>
    </div>

    <div id="k3">
        <h3>KNN With Overlap</h3>
        <table class="table table-bordered table-striped table-hovered table-bordered mx-auto
        " style="width: 250px;">
        <tr>
            <th>Tweets</th>
            <th>Sentiment Original</th>
            <th>Sentiment System</th>
            <th>Valid</th>
        </tr>
    <?php
    $arr_ori3 = array();
    $arr_knn3 = array();
    $valid3 = 0;
    $novalid3 = 0;

    for ($i = 0; $i < count($arr_o); $i++) {
        echo "<tr>";
        echo "<td>".$dataset_test[$i]."</td>";
        echo "<td>" . $labels_test[$i] . "</td>";
        array_push($arr_knn3,$arr_o[$i][0]);
        echo "<td>" . $arr_o[$i][0] . "</td>";
        if ($labels_test[$i] == $arr_o[$i][0]) {
            echo "<td>V</td>";
            $valid3++;
        } else {
            echo "<td>X</td>";
            $novalid3++;
        }
        echo "</tr>";
    }
    $accuracy3 = 100 * (Accuracy::score($labels_test, $arr_knn3));

    $koneksi->close(); 

    ?>
    </table>
    <p>Jumlah Data Testing: <?php echo count($dataset_test); ?></p>
        <p>Jumlah Valid: <?php echo $valid3; ?></p>
        <p>Akurasi: <?php echo $accuracy3 . "%"; ?></p>
    </div>
</body>

</html>