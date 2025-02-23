<?php

namespace App\Http\Controllers;

use App\Models\Chirp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Hashtag;

class ChirpController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        return Inertia::render('Chirps/Index', [
            'chirps' => Chirp::with('user:id,name')->latest()->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|max:255',
            'media' => 'nullable|file|mimes:jpg,jpeg,png,gif,mp4,avi,mov|max:10240', // Validate for image or video
        ]);

        // Initialize an array for storing chirp data
        $data = [
            'message' => $validated['message'],
        ];

        // If media diupload, akan ditaruh tempat penyimapnan storage
        if ($request->hasFile('media')) {
            $mediaPath = $request->file('media')->store('chirps', 'public');  // Store file in 'public/chirps'
            $data['media_url'] = $mediaPath;  // Save the file path in the database
        }

        // Create the chirp with the validated data
        $chirp = $request->user()->chirps()->create($data);

        // Extract hashtags and associate them with the chirp
        $this->handleHashtags($validated['message'], $chirp);

        return redirect(route('chirps.index'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Chirp $chirp)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Chirp $chirp)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Chirp $chirp): RedirectResponse
    {
        Gate::authorize('update', $chirp);

        $validated = $request->validate([
            'message' => 'required|string|max:255',
        ]);

        $chirp->update($validated);

        return redirect(route('chirps.index'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Chirp $chirp): RedirectResponse
    {
        Gate::authorize('delete', $chirp);

        $chirp->delete();

        return redirect(route('chirps.index'));
    }

    /**
     * Handle hashtag extraction and association.
     *
     * @param string $message
     * @param Chirp $chirp
     */
    protected function handleHashtags($message, $chirp)
    {
        preg_match_all('/#(\w+)/', $message, $matches);
        $hashtags = $matches[1]; // Extract hashtags from message

        foreach ($hashtags as $tag) {
            $hashtag = Hashtag::firstOrCreate(['name' => $tag]);
            $chirp->hashtags()->attach($hashtag); // Associate hashtag with chirp
        }
    }
}
