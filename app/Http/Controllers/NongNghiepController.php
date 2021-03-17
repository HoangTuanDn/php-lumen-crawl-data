<?php

namespace App\Http\Controllers;

use App\Model\NewsModel;
use App\Models\PostModel;
use Illuminate\Support\Facades\DB;

class NongNghiepController
{
    public function rss($data)
    {
        $rss = simplexml_load_file($data['source'], null, LIBXML_NOCDATA);
        $domain = [
            'https://nongnghiep.vn',
            'https://nongsanviet.nongnghiep.vn'
        ];
        $content = [];

        foreach ($rss->channel->item as $item) {
            $title = (string)$item->title;
            $link = (string)$item->link;
            $intro = (string)$item->description;
            $author = (string)$item->author;

            $pubDate = (string)$item->pubDate;
            $date_added = date_create($pubDate, timezone_open('Asia/Ho_Chi_Minh'));
            $date_added = date_format($date_added,'Y-m-d H:i');

            $content = (string)$item->children('content', true);

            $imgClass = 'content';
            if (str_contains($link, $domain[1])) {
                $imgClass = 'content_detail';
            }
            $image = $this->rssImage($link, $imgClass);

            $rssListPost [] = [
                'title'       => $title ?? '',
                'source'      => $link ?? '',
                'description' => $content ?? '',
                'intro'       => $intro ?? '',
                'images'      => $image ?? '',
                'author'      => $author ?? '',
                'category_id' => $data['category_id'],
                'date_added'  => $date_added ?? '',
                'date_update' => date('Y-m-d H:i', strtotime('now')),
            ];
        }

        try {
            foreach ($rssListPost as $item) {
                if (!PostModel::isPostRawExist($item['title'])) {
                    //dump([$item['source'], $item['date_added']]);
                    PostModel::insertRawPost($item);
                    echo "\n Success insert: {$item['title']} ";
                }
            }

        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    public function listPost($data)
    {
        $doc = new \DOMDocument();
        $doc->preserveWhiteSpace = false;
        $doc->formatOutput = true;
        @$doc->loadHTML(mb_convert_encoding(file_get_contents($data['source']), 'HTML-ENTITIES', 'UTF-8'));

        $xpath = new \DOMXPath($doc);

        $list_new_post = [];
        foreach ($xpath->query('//ul[@class="list-news-home"]/li[@class="news-home-item"]') as $Node) {
            $list_new_post[] = $doc->saveHTML($Node);
        }

        foreach ($list_new_post as $node) {
            @$doc->loadHTML(mb_convert_encoding($node, 'HTML-ENTITIES', 'UTF-8'));

            $aNode = $doc->getElementsByTagName('a');

            //link
            $link = $aNode->length ? $aNode->item(0)->getAttribute('href') : '';

            //category
            $category = $aNode->length ? $aNode->item(2)->getAttribute('title') : '';
            if ($category === 'Phân bón') continue;

            //title
            $h3Node = $doc->getElementsByTagName('h3');
            $title = $h3Node->length ? trim($h3Node->item(0)->nodeValue) : '';

            if (PostModel::isPostRawExist($title)) {
                continue;
            }

            if (!empty($link)) {
                $this->index($link, $title, $data['category_id']);
                echo "\n Success insert: {$title} ";
            }

        }

    }

    public function index($source, $title, $category_id)
    {
        $doc = new \DOMDocument();
        $doc->preserveWhiteSpace = false;
        $doc->formatOutput = true;
        @$doc->loadHTML(mb_convert_encoding(file_get_contents($source), 'HTML-ENTITIES', 'UTF-8'));

        $xpath = new \DOMXPath($doc);

        $listNode = [];

        foreach ($xpath->query('//div[@class="detail-content"]') as $Node) {
            $listNode[] = $doc->saveHTML($Node);
        }

        $postContent = [];
        foreach ($listNode as $item) {
            @$doc->loadHTML(mb_convert_encoding($item, 'HTML-ENTITIES', 'UTF-8'));

            if ($title) {
                //description
                $pNodes = $doc->getElementsByTagName('p');
                $intro = '';
                $author = '';
                if ($pNodes->length) {
                    foreach ($pNodes as $node) {
                        if ($node->getAttribute('class') == 'content-author') {
                            $author = trim($node->nodeValue);
                        }

                        if ($node->getAttribute('class') == 'main-intro detail-intro') {
                            $intro = trim($node->nodeValue);
                        }
                    }
                }

                // get date
                $date_added = '';
                foreach (preg_split("/\r\n|\n|\r/", $doc->saveHTML()) as $item) {
                    $tmpTime = $xpath->query('//span[@class="time-detail"]');
                    $tmpTime = $tmpTime->length ? trim($tmpTime->item(0)->nodeValue) : '';

                    preg_match('/\d+\/\d+\/\d+\s*,\s*\d+:\d+/', $tmpTime, $date_added);
                    //$date_added = "{$tmpTime[3]} {$tmpTime[5]}";
                    $date_added = date('Y-m-d H:i', strtotime(str_replace('/', '-', $date_added[0])));
                    //$date_added = $tmpTime;
                }

                // get image
                $nodeImage = $xpath->query('//div[@class="content"]//img');
                $images = [];

                foreach ($nodeImage as $node) {
                    $images [] = $node->getAttribute('data-src');
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
        } else {
            echo "\nFail copy Link: {$source}";
        }

    }

    private function detailContent($link)
    {
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
        foreach ($xpath->query('//div[@class="content"]//div[@class="adv"]') as $node) {
            if ($node) {
                $node->parentNode->removeChild($node);
            }
        }

        $detail = [];
        foreach ($xpath->query('//div[@class="content"]/*') as $node) {
            $detail [] = $doc->saveHTML($node);
        }

        return implode('', $detail);
    }

    private function rssImage($link, $class)
    {
        $doc = new \DOMDocument();
        $doc->preserveWhiteSpace = false;
        $doc->formatOutput = true;

        @$doc->loadHTML(mb_convert_encoding(file_get_contents($link), 'HTML-ENTITIES', 'UTF-8'));
        $xpath = new \DOMXPath($doc);

        $nodeImage = $xpath->query('//div[@class="' . $class . '"]//img');
        $images = [];

        foreach ($nodeImage as $node) {
            $images [] = $node->getAttribute('data-src');
        }

        return json_encode($images);

    }
}