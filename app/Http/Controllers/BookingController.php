<?php

namespace App\Http\Controllers;

use App\Events\GeneralNotification;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use App\Models\Booking;


class BookingController extends Controller
{
    // Fetch all bookings
    public function index()
    {
        return response()->json(Booking::orderBy('created_at', 'desc')->get(), 200);
    }

    // Store a new booking
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'datetime' => 'required|date',
            'service' => 'required|string',
            'contact_number' => 'required|string|max:11'
        ]);

        $booking = Booking::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'datetime' => $validated['datetime'],
            'service' => $validated['service'],
            'contact_number' => $validated['contact_number'],
            'status' => 'Pending' // Default status
        ]);


        // 🔔 Notify admin

        // event(new GeneralNotification(
        //     to: 'admin',
        //     message: "📅 New booking from {$booking->name} for {$booking->service}",
        //     type: 'booking'
        // ));

        return response()->json([
            'message' => 'Booking confirmed!',
            'booking' => $booking
        ], 201);
    }

    // Update booking status
    public function updateStatus(Request $request, $id)
    {
        try {
            $booking = Booking::findOrFail($id);

            // Validate the request
            $validated = $request->validate([
                'status' => 'required|in:Pending,Approved,Declined'
            ]);

            // Update status
            $booking->status = $validated['status'];
            $booking->save();

            // 🔔 Notify the user
            // event(new GeneralNotification(
            //     to: 'user',
            //     message: "📝 Your booking status has been updated to '{$booking->status}'.",
            //     type: 'booking',
            //     userId: $booking->email // Optional: use user ID if available
            // ));

            return response()->json([
                'message' => 'Booking status updated successfully!',
                'booking' => $booking
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to update booking status',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getUserBookings(Request $request)
    {
        try {
            // Validate that email exists
            $userEmail = $request->query('email');

            if (!$userEmail) {
                return response()->json(['error' => 'User email is required'], 400);
            }

            // Get only bookings for this email, sorted in descending order (newest first)
            $bookings = Booking::where('email', $userEmail)
                ->orderBy('created_at', 'desc') // ✅ Sort by newest first
                ->get();

            return response()->json($bookings, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch bookings', 'message' => $e->getMessage()], 500);
        }
    }



    public function getBookingReport()
    {
        try {
            // Fetch only approved bookings
            $approvedBookings = Booking::where('status', 'Approved')->get();

            $totalRevenue = $approvedBookings->sum(callback: function ($booking) {
                $subscriptionPlan = SubscriptionPlan::where('name', $booking->service)->first();
                return $subscriptionPlan ? $subscriptionPlan->price : 0; // Return price if found, else 0
            });

            // Count total approved bookings
            $totalBookings = $approvedBookings->count();

            // Count unique active clients
            $activeClients = Booking::where('status', 'Approved')->distinct('email')->count('email');

            return response()->json([
                'totalRevenue' => $totalRevenue, // ✅ Now based on actual plan prices
                'totalBookings' => $totalBookings,
                'activeClients' => $activeClients
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch approved booking report',
                'message' => $e->getMessage()
            ], 500);
        }
    }



    public function exportCSV()
    {
        try {
            \Log::info("🚀 Exporting Booking CSV...");
            $bookings = Booking::all();

            if ($bookings->isEmpty()) {
                \Log::warning("⚠️ No bookings found.");
                return response()->json(['error' => 'No bookings available.'], 404);
            }

            $csvFileName = 'bookings_report.csv';
            $headers = [
                "Content-Type" => "text/csv; charset=UTF-8",
                "Content-Disposition" => "attachment; filename=$csvFileName",
                "Pragma" => "no-cache",
                "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                "Expires" => "0"
            ];

            return response()->stream(function () use ($bookings) {
                $handle = fopen('php://output', 'w');
                if (!$handle) {
                    throw new \Exception("🚨 Unable to open php://output for writing.");
                }

                // ✅ Add UTF-8 BOM to fix encoding issues in Excel
                fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

                // ✅ LOGO & TITLE (Excel will auto-bold first row)
                fputcsv($handle, [" "]); // Empty row for spacing
                fputcsv($handle, ["ARBuildTech"]);
                fputcsv($handle, ["BOOKING REPORT"]);
                fputcsv($handle, ["Date: " . now()->format('d M Y')]);
                fputcsv($handle, [" "]); // Empty row

                // ✅ HEADER ROW
                fputcsv($handle, ["Name", "Email", "Service", "Date & Time", "Status"]);

                foreach ($bookings as $booking) {
                    fputcsv($handle, [
                        $booking->name,
                        $booking->email,
                        $booking->service,
                        date('d M Y, h:i A', strtotime($booking->datetime)),
                        $booking->status,
                    ]);
                }

                fclose($handle);
            }, 200, $headers);
        } catch (\Exception $e) {
            \Log::error("❌ Booking CSV Export Error: " . $e->getMessage());
            return response()->json(['error' => 'Failed to generate CSV', 'details' => $e->getMessage()], 500);
        }
    }




    // fucntion ng report
    public function getApprovedBookingsForExport(Request $request)
    {
        try {
            // ✅ Get start & end date from request (default: last 30 days)
            $startDate = $request->query('startDate', now()->subDays(30)->toDateString());
            $endDate = $request->query('endDate', now()->toDateString());

            // ✅ Fetch only approved bookings within the date range
            $approvedBookings = Booking::where('status', 'Approved')
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->get();

            // ✅ Process bookings to include price from SubscriptionPlan
            $bookingsData = $approvedBookings->map(function ($booking) {
                $subscriptionPlan = SubscriptionPlan::where('name', $booking->service)->first();
                $price = $subscriptionPlan ? floatval($subscriptionPlan->price) : 0; // Convert to float
                return [
                    'name' => $booking->name,
                    'service' => $booking->service,
                    'price' => number_format(abs($price), 2, '.', ''), // ✅ Fix ± issue & force proper formatting
                ];
            });

            // ✅ Calculate total price (ensure correct rounding)
            $totalPrice = $bookingsData->sum(function ($booking) {
                return floatval(str_replace(',', '', $booking['price'])); // Ensure no formatting issues
            });

            return response()->json([
                'bookings' => $bookingsData,
                'totalPrice' => number_format(abs($totalPrice), 2, '.', ''), // ✅ Ensure clean total price formatting
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch approved bookings for export',
                'message' => $e->getMessage()
            ], 500);
        }
    }


}
