<?php

namespace App\Http\Controllers;

use App\Http\Resources\MessageCollection;
use App\Http\Resources\MessageResource;
use App\Models\Message;
use App\Models\User;
use App\Utils\HttpStatusCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class MessageController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'per_page' => 'integer',
            'page' => 'integer'
        ]);

        $messages = Message::with('createdBy')->paginate($request->get('per_page', 12));
        return (new MessageCollection($messages))->response()->setStatusCode(HttpStatusCode::OK);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'email' => 'required|email|max:255',
                'name' => 'required|string|max:255',
                'content' => 'required|string|max:255'
            ]);

            $user = User::firstOrCreate(
                [ 'email' => $validated['email'] ],
                [ 'name' => $validated['name'] ]
            );

            $message = new Message();
            $message->content = $validated['content'];
            $message->created_by = $user->id;
            $message->save();

            $message->load('createdBy');

            DB::commit();

            return (new MessageResource($message))->response()->setStatusCode(HttpStatusCode::CREATED);
        } catch (ValidationException $validationException) {
            DB::rollBack();
            throw $validationException;
        } catch (Throwable $throwable) {
            DB::rollBack();
            throw $throwable;
        }
    }

    public function show(int $id): JsonResponse
    {
        $message = new MessageResource(Message::with('createdBy')->findOrFail($id));
        return (new MessageResource($message))->response()->setStatusCode(HttpStatusCode::OK);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'content' => 'required|string|max:255'
        ]);

        $message = Message::with('createdBy')->findOrFail($id);
        $message->fill($validated);
        $message->save();

        return (new MessageResource($message))->response()->setStatusCode(HttpStatusCode::OK);
    }

    public function destroy($id): JsonResponse
    {
        $message = Message::findOrFail($id);
        $message->delete();

        return response()->json(['data' => null])->setStatusCode(HttpStatusCode::OK);
    }
}
