<?php

namespace App\Http\Controllers\WEB\CMS;

use App\Models\CmsContent;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class HeroSectionController extends Controller
{
    /**
     * Display the hero section edit form
     */
    public function form()
    {
        $cms = CmsContent::where([
            'page_slug' => 'landing-page',
            'section'   => 'hero',
        ])->first();

        return view('backend.cms.hero', compact('cms'));
    }

    /**
     * Store or update the hero section
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'btn_text'    => 'nullable|string|max:100',
            'btn_link'    => 'nullable|url|max:255',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status'      => 'nullable|boolean',
        ]);

        $data = $validated;

        // Status: 1 = active (checked), 0 = inactive (unchecked)
        // Default to active (1) when creating new entry
        $data['status'] = $request->boolean('status', true);

        // Treat empty button link as null in database
        if (empty(trim($data['btn_link'] ?? ''))) {
            $data['btn_link'] = null;
        }

        // Handle image upload & old image removal
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            // Find existing record to delete old image if exists
            $existing = CmsContent::where([
                'page_slug' => 'landing-page',
                'section'   => 'hero',
            ])->first();

            // Delete old image from storage
            if ($existing && $existing->image_path) {
                Storage::disk('public')->delete($existing->image_path);
            }

            // Store new image
            $data['image_path'] = $request->file('image')
                ->store('cms/hero', 'public');
        }

        // Optional: allow removing image without uploading new one
        if ($request->boolean('delete_image') && isset($existing) && $existing->image_path) {
            Storage::disk('public')->delete($existing->image_path);
            $data['image_path'] = null;
        }

        // Save / update record
        CmsContent::updateOrCreate(
            [
                'page_slug' => 'landing-page',
                'section'   => 'hero',
            ],
            array_merge($data, [
                'type'  => 'single',
                'order' => 1,
            ])
        );

        return back()->with('success', 'Hero section updated successfully!');
    }
}
