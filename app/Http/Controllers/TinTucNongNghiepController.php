<?php


namespace App\Http\Controllers;


use App\Models\PostModel;

class TinTucNongNghiepController extends Controller
{
    public function listPost($data){
        $doc = new \DOMDocument();
        $doc->preserveWhiteSpace = false;
        $doc->formatOutput = true;
        @$doc->loadHTML(mb_convert_encoding(file_get_contents($data['source']), 'HTML-ENTITIES', 'UTF-8'));

        $xpath = new \DOMXPath($doc);

        $list_news_post = [];
        foreach ($xpath->query('//div[@class="blog-posts hfeed"]/div[@class="date-outer"]') as $Node) {
            $list_news_post[] = $doc->saveHTML($Node);
        }

        foreach ($list_news_post as $node) {
            @$doc->loadHTML(mb_convert_encoding($node, 'HTML-ENTITIES', 'UTF-8'));

            $aNode = $doc->getElementsByTagName('a');

            //link
            $link = $aNode->length ? $aNode->item(0)->getAttribute('href') : '';

            //title
            $h3Node = $doc->getElementsByTagName('h2');
            $title = $h3Node->length ? trim($h3Node->item(0)->nodeValue) : '';

            //description
            $divNode = $doc->getElementsByTagName('div');
            $intro = '';

            if ($divNode->length){
                foreach ($divNode as $node){
                    if($node->getAttribute('class') == 'snippet'){
                        $intro = trim($node->nodeValue);
                    }
                }
            }

            if (PostModel::isPostRawExist($title)){
                continue;
            }

            if(!empty($link)){
                $this->index($link,$title, $intro, $data['category_id']);
                echo "\n Success insert: {$title} ";
            }

        }
    }

    public function index($source, $title, $intro, $category_id){
        $doc = new \DOMDocument();
        $doc->preserveWhiteSpace = false;
        $doc->formatOutput = true;
        @$doc->loadHTML(mb_convert_encoding(file_get_contents($source), 'HTML-ENTITIES', 'UTF-8'));

        $xpath = new \DOMXPath($doc);

        $listNode = [];

        foreach ($xpath->query('//div[@class="post-inner entry"]') as $Node) {
            $listNode[] = $doc->saveHTML($Node);
        }

        $postContent = [];
        foreach ($listNode as $item) {
            @$doc->loadHTML(mb_convert_encoding($item, 'HTML-ENTITIES', 'UTF-8'));

            $tmpTitle = $doc->getElementsByTagName('h1');

            if ($title) {
                //description
                $author = '';

                // get date
                $date_added = '';
                foreach (preg_split("/\r\n|\n|\r/", $doc->saveHTML()) as $item) {
                    $tmpTime = $xpath->query('//abbr[@class="timeago"]');
                    $tmpTime = $tmpTime[0] ? $tmpTime[0]->getAttribute('title') : '';
                    $date_added = date_create($tmpTime);
                    $date_added = date_format($date_added,'Y-m-d H:i');
                }


                // get image
                $nodeImage = $xpath->query('//div[@class="post-body"]//img');
                $images = [];

                foreach ($nodeImage as $node) {
                    $images [] = $node->getAttribute('src');
                }

                //get content
                $detail = $this->detailContent($source);

                $postContent = [
                    'title'       => $title,
                    'intro'       => $intro,
                    'source'      => $source,
                    'author'      => $author,
                    'description' => $detail,
                    'images'      => json_encode($images),
                    'category_id' => $category_id,
                    'date_added'  => $date_added,
                    'date_update' => date('Y-m-d H:i', strtotime('now')),
                ];
            }
        }

        if ($postContent) {
            try {
                //dump([$postContent['source'], $postContent['date_added']]);
                PostModel::insertRawPost($postContent);
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
        }else{
            echo "\nFail copy Link: {$source}";
        }
    }

    private function detailContent($link){
        $doc = new \DOMDocument();
        $doc->preserveWhiteSpace = false;
        $doc->formatOutput = true;

        @$doc->loadHTML(mb_convert_encoding(file_get_contents($link), 'HTML-ENTITIES', 'UTF-8'));
        $xpath = new \DOMXPath($doc);

        //remove node from content
        $scriptNode = $doc->getElementsByTagName('script');
        if ($scriptNode->length) {
            for ($i = $scriptNode->length; --$i >= 0;) {
                $e = $scriptNode->item($i);
                $e->parentNode->removeChild($e);
            }
        }
        foreach ($xpath->query('//div[@class="post-body"]//div[@class="google-auto-placed ap_container"]') as $node) {
            if ($node) {
                $node->parentNode->removeChild($node);
            }
        }

        //get content
        $detail = [];
        foreach ($xpath->query('//div[@class="post-body"]/*') as $node) {
            $detail [] = $doc->saveHTML($node);
        }

        return implode('', $detail);
    }
}