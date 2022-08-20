<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Filter\FilterController;
use App\Models\Article;
use App\Http\Requests\StoreArticleRequest;
use App\Http\Requests\UpdateArticleRequest;
use App\Models\Department;
use App\Repositories\ArticleRepository;
use App\Repositories\Filter\FilterRepository;
use \Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PHPUnit\Util\Filter;

class ArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, ArticleRepository $articleRepository, $isPublished = 0, $showDeleted = 0)
    {
        $user = $request->user();
        $results = structuredJson($articleRepository->index($user, $isPublished, $showDeleted));
        return response()->json($results[0], $results[1], $results[2], $results[3]);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(FilterController $filterController, Request $request)
    {
        $results = structuredJson($filterController->retrieveDepartments());
        return response()->json($results[0], $results[1], $results[2], $results[3]);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\StoreArticleRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreArticleRequest $request, ArticleRepository $articleRepository)
    {
        $validated = $request->safe()->only(['author', 'title', 'contributors', 'body', 'publishedArticles', 'categories', 'tags', 'messages']);
        $results = structuredJson($articleRepository->create($validated));
        return response()->json($results[0], $results[1], $results[2], $results[3]);
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Article $article
     * @return \Illuminate\Http\Response
     */
    public function show(Article $article)
    {
        /*
         * Only the main part of the article which is title and body sections are applicable for the
         * users who want to read the article and the title and body of the children articles of that
         * article is not shown to them.
         */
        $results = structuredJson($article);
        return response()->json($results[0], $results[1], $results[2], $results[3]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Article $article
     * @return \Illuminate\Http\Response
     */
    public function edit(ArticleRepository $articleRepository, Article $article, $revisionNumber = null)
    {
        $results = structuredJson($articleRepository->editArticle($article, $revisionNumber));
        return response()->json($results[0], $results[1], $results[2], $results[3]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\UpdateArticleRequest $request
     * @param \App\Models\Article $article
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateArticleRequest $request, Article $article, ArticleRepository $articleRepository)
    {
        $request = $request->validated();
        $results = structuredJson($articleRepository->updateArticle($request, $article));
        return response()->json($results[0], $results[1], $results[2], $results[3]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Article $article
     * @return \Illuminate\Http\Response
     */
    public function destroy(Article $article, ArticleRepository $articleRepository)
    {
        $results = structuredJson($articleRepository->softDelete($article));
        return response()->json($results[0], $results[1], $results[2], $results[3]);
    }

    public function restore(Article $article, ArticleRepository $articleRepository){
        $results = structuredJson($articleRepository->restoreDeleted($article));
        return response()->json($results[0], $results[1], $results[2], $results[3]);
    }

    public function forceDelete(Article $article, ArticleRepository $articleRepository)
    {
        $results = structuredJson($articleRepository->forceDelete($article));
        return response()->json($results[0], $results[1], $results[2], $results[3]);
    }

    public function invitationResponse(ArticleRepository $articleRepository, $articleID, $userID, $parameter)
    {
        $results = structuredJson($articleRepository->invitationResponse($articleID, $userID, $parameter));
        return response()->json($results[0], $results[1], $results[2], $results[3]);

    }

    public function deleteContributor(UpdateArticleRequest $request, ArticleRepository $articleRepository)
    {
        $validated = $request->safe()->only('articleID', 'contributors');
        $results = structuredJson($articleRepository->deleteContributor($validated));
        return response()->json($results[0], $results[1], $results[2], $results[3]);

    }

}
