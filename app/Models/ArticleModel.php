<?php

namespace App\Models;

use App\Entities\Article;
use CodeIgniter\Model;
use Config\Services;

class ArticleModel extends Model
{
    public static $categories = [
        'news',
        'info',
        'page',
        'draft',
    ];

    protected $table         = 'article';
    protected $allowedFields = [
        'title', 'content', 'category', 'user_id'
    ];
    protected $primaryKey = 'id';
    protected $returnType = 'App\Entities\Article';
    protected $useTimestamps = true;

    public function withCategory($cat)
    {
        $this->builder()->where('category', $cat);
        return $this;
    }

    public function withSearch($q)
    {
        $this->builder()->like('content', $q);
        $this->builder()->orLike('title', $q);
        return $this;
    }

    public function withUser($id)
    {
        $this->builder()->where('user_id', $id);
        return $this;
    }

    public function processWeb($id)
    {
        if ($id === null) {
            $item = (new Article($_POST));
            $item->user_id = Services::login()->id;
            return $this->insert($item);
        } else if ($item = $this->find($id)) {
            /** @var Article $item */
            $item->fill($_POST);
            if ($item->hasChanged()) {
                $this->save($item);
            }
            return $id;
        }
        return false;
    }

    public function getArticlesWithUserNames()
    {
        return $this->select('article.*, user.name as user_name') // Pilih semua kolom dari articles, dan kolom 'name' dari users sebagai 'user_name'
                    ->join('user', 'user.id = article.user_id') // Lakukan JOIN pada user_id dan id user
                    ->findAll(); // Ambil semua data
    }

}
