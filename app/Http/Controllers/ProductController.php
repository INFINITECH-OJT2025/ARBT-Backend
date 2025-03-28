<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage; // ✅ Import Storage facade

class ProductController extends Controller
{
    // // Fetch all products
    // public function index()
    // {
    //     \Log::info("🔍 Fetching products without authentication."); // ✅ Log request

    //     $products = Product::where('status', 'Active')->get();

    //     foreach ($products as $product) {
    //         $product->image = $product->image ? url("storage/" . $product->image) : null;
    //     }

    //     return response()->json($products, 200);
    // }

    public function index(Request $request)
{
    \Log::info("🔍 Fetching products based on status filter."); // ✅ Log request

    // Get the 'status' parameter from the query string, default to 'Active'
    $status = $request->query('status', 'Active'); // If no status is provided, default to 'Active'

    // Fetch products based on the provided 'status'
    $products = Product::where('status', $status)->get();

    // Format the image URLs
    foreach ($products as $product) {
        $product->image = $product->image ? url("storage/" . $product->image) : null;
    }

    return response()->json($products, 200);
}

    // Store a new product
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'price' => 'required|numeric',
            'quantity' => 'required|integer',
            'image' => 'nullable|image|max:2048', // ✅ Ensure it's an image
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('public/products'); // ✅ Save to `storage/app/public/products`
        }

        $product = Product::create([
            'name' => $request->name,
            'price' => $request->price,
            'quantity' => $request->quantity,
            'tag' => $request->tag,
            'description' => $request->description,
            'status' => $request->status ?? 'Active',
            'image' => $imagePath ? str_replace('public/', '', $imagePath) : null, // ✅ Store correct path
        ]);

        return response()->json($product, 201);
    }





    // ✅ Get a single product
    public function show($id)
    {
        $product = Product::find($id);
        return $product ? response()->json($product, 200) : response()->json(['message' => 'Not found'], 404);
    }

    // ✅ Update product
    public function update(Request $request, $id)
    {
        \Log::info("🔍 Updating Product ID: " . $id);

        $product = Product::findOrFail($id);

        // ✅ Validate fields (only check fields that are sent)
        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'price' => 'sometimes|required|numeric|min:0',
            'quantity' => 'sometimes|required|integer|min:1',
            'tag' => 'nullable|string',
            'description' => 'nullable|string',
            'status' => 'sometimes|required|string|in:Active,Archived',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // ✅ Handle Image Upload (if updated)
        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::delete('public/' . $product->image); // Delete old image
            }
            $imagePath = $request->file('image')->store('products', 'public');
            $validatedData['image'] = $imagePath;
        }

        // ✅ Update only provided fields
        $product->update($validatedData);

        return response()->json([
            'message' => 'Product updated successfully!',
            'product' => $product,
        ], 200);
    }



    // ✅ Delete product
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        if ($product->image)
            Storage::delete('public/' . $product->image);
        $product->delete();
        return response()->json(['message' => 'Product deleted!'], 200);
    }





    public function exportCSV()
    {
        try {
            \Log::info("🚀 Exporting CSV...");
            $products = Product::all();

            if ($products->isEmpty()) {
                \Log::warning("⚠️ No products found.");
                return response()->json(['error' => 'No products available.'], 404);
            }

            $csvFileName = 'products_report.csv';
            $headers = [
                "Content-Type" => "text/csv; charset=UTF-8",
                "Content-Disposition" => "attachment; filename=$csvFileName",
                "Pragma" => "no-cache",
                "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                "Expires" => "0"
            ];

            return response()->stream(function () use ($products) {
                $handle = fopen('php://output', 'w');
                if (!$handle) {
                    throw new \Exception("🚨 Unable to open php://output for writing.");
                }

                // ✅ Add UTF-8 BOM to fix encoding issues in Excel
                fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

                // ✅ Add an empty row to separate title
                fputcsv($handle, [" "]); // Empty row for spacing
                fputcsv($handle, ["ARBuildTech"]); // This will be bold if opened in Excel
                fputcsv($handle, ["PRODUCT INVENTORY REPORT"]); // Excel will detect it as bold
                fputcsv($handle, ["Date: " . now()->format('d M Y')]); // ✅ "10 Mar 2025" format
                fputcsv($handle, [" "]); // Empty row for spacing

                // ✅ HEADER ROW (Excel will automatically bold it)
                fputcsv($handle, ["Name", "Quantity", "Price (₱)", "Total Price (₱)"]);

                $grandTotal = 0;

                foreach ($products as $product) {
                    $totalPrice = $product->price * $product->quantity;
                    $grandTotal += $totalPrice;

                    fputcsv($handle, [
                        $product->name,
                        $product->quantity,
                        number_format($product->price, 2),
                        number_format($totalPrice, 2), // ✅ Add ₱ symbol
                    ]);
                }

                fputcsv($handle, [" "]); // Empty row for spacing
                fputcsv($handle, ["TOTAL", " ", " ", "₱" . number_format($grandTotal, 2)]); // ✅ TOTAL BOLD in Excel

                fclose($handle);
            }, 200, $headers);
        } catch (\Exception $e) {
            \Log::error("❌ CSV Export Error: " . $e->getMessage());
            return response()->json(['error' => 'Failed to generate CSV', 'details' => $e->getMessage()], 500);
        }
    }


}
