<?php
require_once __DIR__ . '/vendor/autoload.php';

use Phpml\FeatureExtraction\TokenCountVectorizer;
use Phpml\Tokenization\WhitespaceTokenizer;
use Phpml\FeatureExtraction\TfIdfTransformer;
use Phpml\Math\Distance\Jaccard;
use Phpml\Math\Distance\Asymmetric;
use Phpml\Math\Distance\Overlap;
use Phpml\Classification\KNearestNeighbors;

class Sentiment 
{
    public function sentiment(array $effected_id, string $coeff)
    {
        $koneksi = new mysqli("localhost", "root", "", "project_iir");

        if ($koneksi->connect_errno) {
            die("Failed to connect to MySQL: " . $koneksi->connect_error);
        }

        $stemmerfactory = new \Sastrawi\Stemmer\StemmerFactory();
        $stemmer = $stemmerfactory->createStemmer();

        $stopwordFactory = new \Sastrawi\StopWordRemover\StopWordRemoverFactory();
        $stopword = $stopwordFactory->createStopWordRemover();

        for ($batas = $effected_id[0]; $batas <= $effected_id[count($effected_id)-1]; $batas++) {
            $res_dataset = "";
            $res_crawl = "";
            $sample_data_tweet = [];
            $sample_data_labels = [];

            $sql_crawl = "SELECT * FROM tweet WHERE tweet_id = $batas";
            $sql_dataset = "SELECT * FROM tweet WHERE tweet_id < $batas";

            $res_dataset = $koneksi->query($sql_dataset);
            $res_crawl = $koneksi->query($sql_crawl);

            $result="";

            while ($baris = $res_crawl->fetch_assoc()) {
                while ($baris_data = $res_dataset->fetch_assoc()) {
                    //preprocessed dataset
                    $stemTweet = $stemmer->stem($baris_data['content']);
                    $stopTweet = $stopword->remove($stemTweet);

                    array_push($sample_data_tweet, $stopTweet);
                    array_push($sample_data_labels, $baris_data['isPositive']);
                }


                //preprocessed hasil crawl
                $stemTweet = $stemmer->stem($baris['content']);
                $stopTweet = $stopword->remove($stemTweet);

                array_push($sample_data_tweet, $stopTweet);

                // TF-IDF
                $tf = new TokenCountVectorizer(new WhitespaceTokenizer());
                $tf->fit($sample_data_tweet);
                $tf->transform($sample_data_tweet);
                $vocabulary = $tf->getVocabulary();


                $tfidf = new TfIdfTransformer($sample_data_tweet);
                $tfidf->transform($sample_data_tweet);

                //sample_data_tweet = semua dataset + 1 hasil crawling
                //Sentiment
                $total1 = count($sample_data_tweet);
                $totalNew = count($vocabulary);
                $k_value = $totalNew / 3;

                // get last value
                $lastKey = $sample_data_tweet[$total1 - 1]; //get query

                if($coeff == "Asymmetric"){
                    $classifier = new KNearestNeighbors($k_value,new Asymmetric());

                }
                else if($coeff == "Jaccard") {
                    $classifier = new KNearestNeighbors($k_value,new Jaccard());

                }
                else if ($coeff == "Overlap"){
                    $classifier = new KNearestNeighbors($k_value,new Overlap());
                }

                $classifier->train(array_slice($sample_data_tweet, 0, -1), $sample_data_labels);
                $result = $classifier->predict($lastKey);

            }
            $sql_update = "UPDATE tweet SET isPositive=? WHERE tweet_id=?";
            $stmt = $koneksi->prepare($sql_update);
            $stmt->bind_param("di", $result, $batas);
            $stmt->execute();
        }
        $koneksi->close(); 
    }
}
?>