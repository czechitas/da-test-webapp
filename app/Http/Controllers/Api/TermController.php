<?php

declare(strict_types=1);

namespace CzechitasApp\Http\Controllers\Api;

use CzechitasApp\Http\Controllers\Controller;
use CzechitasApp\Http\Requests\Api\Term\CreateTermRequest;
use CzechitasApp\Http\Requests\Api\Term\UpdateTermRequest;
use CzechitasApp\Models\Term;
use CzechitasApp\Services\Models\TermService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TermController extends Controller
{
    protected TermService $termService;

    public function __construct(TermService $termService)
    {
        $this->termService = $termService;
    }

    public function index(Request $request): Response
    {
        if (Auth::user()->can('list', Term::class)) {
            return \response()->json($this->termService->getApiList(
                (int)$request->query('page', '1'),
                (int)$request->query('perPage', '50'),
            )->get(['id', 'category_id', 'start', 'end', 'opening', 'price']));
        }

        if (Auth::user()->can('listParent', Term::class)) {
            return \response()->json($this->termService->getApiList(
                (int)$request->query('page', '1'),
                (int)$request->query('perPage', '50'),
            )->possibleLogin()->get(['id', 'category_id', 'start', 'end', 'price']));
        }

        \abort(403);
    }

    public function show(Term $term): Response
    {
        if (Auth::user()->can('view', $term)) {
            return \response()->json($term);
        }

        if (Auth::user()->can('viewParent', $term)) {
            return \response()->json($term->only(['id', 'category_id', 'start', 'end', 'price', 'note_public']));
        }

        \abort(403);
    }

    public function store(CreateTermRequest $request): Response
    {
        $this->authorize('create', Term::class);
        $term = $this->termService->insert($request->getData());

        return \response()->json($term, Response::HTTP_CREATED);
    }

    public function update(UpdateTermRequest $request, Term $term): Response
    {
        $this->authorize('update', $term);
        $this->termService->setContext($term)->update($request->getData());

        return \response()->json($this->termService->getContext(), Response::HTTP_OK);
    }

    public function destroy(Term $term): Response
    {
        $this->authorize('delete', $term);
        $this->termService->setContext($term)->delete();

        return \response()->json('OK', Response::HTTP_OK);
    }
}
