<?php


namespace App\Models;


use Illuminate\Support\Facades\DB;

class PostModel
{
    public static function getLinksByCategory($category = '')
    {

        if (!empty($category)) {
            return DB::connection('mysql')
                ->table('article_category')
                ->select('description')
                ->where('name', '=', $category)
                ->get();
        }

        return DB::connection('mysql')
            ->table('article_category')
            ->select('description', 'category_id')
            ->whereNotNull('description')
            ->get();
    }

    public static function isPostRawExist($title)
    {
        $title = DB::connection('mysql')
            ->table('article_post_raw')
            ->select('title')
            ->where('title', '=', $title)
            ->get()->first();

        return $title ? true : false;
    }

    public static function insertPost($data)
    {
        try {

            DB::connection('mysql')
                ->table('article_post_copy')
                ->insert([
                    'name'          => $data['name'],
                    'description'   => $data['description'],
                    'allow_comment' => 1,
                    'featured'      => 0,
                    'viewed'        => 0,
                    'image'         => $data['image'],
                    'meta_keyword'  => $data['source'],
                    'author'        => $data['author'],
                    'sort_order'    => 1,
                    'status'        => 1,
                    'created_at'    => $data['created_at'],
                    'updated_at'    => $data['updated_at'],

                ]);
        } catch (\Exception $e) {
            echo "\n" . $e->getMessage();
        }

    }

    public static function isPostExits($name)
    {
        $dbName = DB::connection('mysql')
            ->table('article_post_copy')
            ->select('name')
            ->where('name', '=', $name)
            ->get()->first();

        return $dbName ? true : false;
    }

    public static function insertRawPost($data)
    {
        return DB::connection('mysql')
            ->table('article_post_raw')
            ->insert([
                'title'        => $data['title'],
                'description' => $data['description'],
                'author'      => $data['author'],
                'link'        => $data['source'],
                'created_at'  => $data['date_added'],
                'update_at'   => $data['date_update'],
                'image'       => $data['images'],
                'intro'       => $data['intro'],
                'category_id' => $data['category_id']
            ]);
    }

    public static function getRawPosts()
    {
        return DB::connection('mysql')
            ->table('article_post_raw')
            ->get();
    }

}
