<?php

namespace App\Observers;

use App\Models\Topic;
use App\Handlers\SlugTranslateHandler;
use App\Notifications\TopicReplied;

// creating, created, updating, updated, saving,
// saved,  deleting, deleted, restoring, restored

class TopicObserver {
    public function creating(Topic $topic) {
        //
    }

    public function updating(Topic $topic) {
        //
    }

    public function saving(Topic $topic) {
        //xss防护
        $topic->body = clean($topic->body, 'user_topic_body');

        //生成话题摘录
        $topic->excerpt = make_excerpt($topic->body);

        //如slug字段无内容，使用翻译器对 slug 进行翻译
        if ( ! $topic->slug) {
            $topic->slug = app(SlugTranslateHandler::class)->translate($topic->title);
        }
    }

    public function deleted(Topic $topic) {
        \DB::table('replies')->where('topic_id', $topic->id)->delete();
    }
}