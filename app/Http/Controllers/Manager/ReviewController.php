<?php

namespace App\Http\Controllers\Manager;

use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReviewController extends ManagerController
{
    public function index(Request $request)
    {
        $branchId = $this->branchId();
        $rating   = Validator::make($request->only('rating'), ['rating' => ['nullable', 'integer', 'between:1,5']])
            ->valid()['rating'] ?? null;

        $stats = Review::ofBranch($branchId)
            ->selectRaw('COUNT(*) as total, AVG(rating) as avg_rating, AVG(delivery_rating) as avg_delivery')
            ->first();
        $distribution = Review::ofBranch($branchId)->selectRaw('rating, COUNT(*) as c')->groupBy('rating')->pluck('c', 'rating');

        $reviews = Review::ofBranch($branchId)
            ->with(['user', 'order'])
            ->when($rating, fn ($q, $r) => $q->where('rating', $r))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('manager.reviews.index', compact('stats', 'distribution', 'reviews', 'rating'));
    }

    public function reply(Request $request, Review $review)
    {
        abort_if((int) $review->branch_id !== $this->branchId(), 404);

        $data = $request->validate(['reply' => ['required', 'string', 'max:1000']], [
            'reply.required' => 'Vui lòng nhập nội dung trả lời.',
            'reply.max'      => 'Trả lời tối đa 1000 ký tự.',
        ]);

        $review->update(['reply' => $data['reply'], 'replied_at' => now()]);

        return back()->with('success', 'Đã lưu trả lời.');
    }
}
