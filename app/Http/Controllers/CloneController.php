<?php

namespace App\Http\Controllers;

use App\Models\PostModel;
use Illuminate\Support\Facades\DB;
use function PHPUnit\Framework\isEmpty;

class CloneController extends Controller
{
    private $cli_out = '';

    public function post($setting = [])
    {
        if (isset($setting['out'])) {
            $this->cli_out = $setting['out'];
        }

        //get raw data
        $this->getRawPost();

        //process data

        /*$dbResults = $dbResults = PostModel::getRawPosts();

        if ($dbResults) {
            foreach ($dbResults as $raw) {

                if (PostModel::isPostExits($raw->title)) {
                    continue;
                }

                $this->processRawPost($raw);
            }
        }*/

    }

    private function processRawPost($raw)
    {
        $name = $raw->title;
        $intro = $raw->intro;
        $description = $raw->description;
        $created_at = $raw->created_at;
        $updated_at = date('Y-m-d H:i:s', strtotime('now'));
        $link = $raw->link;
        $images = $raw->image != '[]' ? json_decode($raw->image, true) : '';
        $author = $raw->author;
        $htmlIntro = '';
        $htmlAuthor = '';

        if (!empty($intro)) {
            $htmlIntro = '<p><strong>' . $intro . '</strong></p>';
        }
        if (!empty($author)) {
            $htmlAuthor = '<p style="text-align: right;"><strong>' . $author . '</strong></p>';
        }
        if (!empty($description && !empty($intro) && !empty($author))) {
            $description = $htmlIntro . $description . $htmlAuthor;
        }

        if (!empty($images)) {
            $images = $this->copyImageFromUrl($images[0], config('web.server.path'));
        }

        $data = [
            'name'        => $name,
            'description' => $description,
            'created_at'  => $created_at,
            'updated_at'  => $updated_at,
            'source'      => $link,
            'image'       => $images,
            'author'      => $author
        ];

        PostModel::insertPost($data);

        echo "\n Success insert: {$data['name']}";

    }


    private function getRawPost()
    {
        $check = [
            'https://nongnghiep.vn',
            'http://www.tintucnongnghiep.com',
            '.rss',
        ];

        $links = PostModel::getLinksByCategory();

        foreach ($links as $link) {
            if (!empty($link)) {
                if (str_contains($link->description, $check[0])) {

                    if (str_contains($link->description, $check[2])) {
                        app()->call('App\Http\Controllers\NongNghiepController@rss', [
                            'data' => [
                                'source'      => $link->description,
                                'category_id' => $link->category_id
                            ]

                        ]);
                    } else {
                        app()->call('App\Http\Controllers\NongNghiepController@listPost', [
                            'data' => [
                                'source'      => $link->description,
                                'category_id' => $link->category_id
                            ]
                        ]);
                    }
                }

                if (str_contains($link->description, $check[1])) {
                    app()->call('App\Http\Controllers\TinTucNongNghiepController@listPost', [
                        'data' => [
                            'source'      => $link->description,
                            'category_id' => $link->category_id
                        ]
                    ]);
                }
            }
        }
    }

    private function copyImageFromUrl($image_href, $path)
    {

        $content = file_get_contents($image_href);
        $ext = pathinfo($image_href, PATHINFO_EXTENSION);
        $date = date('d-m-Y H:i:s.u', strtotime('now'));
        $file = $path . '/' . "Screen_shot at {$date}.{$ext}";
        $ok = '';
        if (!file_exists($file)) {
            $ok = file_put_contents($file, $content);
        }

        $dir = explode('/', dirname($file));

        return $ok ? $dir[count($dir) - 1] . '/' . basename($file) : '';
    }


    protected function dump($message)
    {
        if (php_sapi_name() == 'cli' && $this->cli_out == 'live') {
            echo $message;
        }
    }
}