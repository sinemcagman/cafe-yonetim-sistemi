<?php

namespace App\Http\Controllers;

use App\Enums\CashMovementType;
use App\Enums\PaymentMethod;
use App\Http\Requests\CashRequest;
use App\Models\CashSession;
use App\Models\Order;
use App\Services\CashService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CashController extends Controller
{
    public function __construct(private readonly CashService $cash)
    {
    }

    /**
     * Güncel kasa: açık oturumun özeti, hareketleri ve ödeme bekleyen siparişler.
     * Kasa kapalıysa açma formu gösterilir.
     */
    public function index(Request $request): View
    {
        $session = $this->cash->currentSession()?->load('opener');

        return view('cash.index', [
            'session' => $session,
            'summary' => $session ? $this->cash->summary($session) : null,
            'movements' => $session
                ? $session->movements()->with(['user', 'payment.order.diningTable'])->latest()->latest('id')->paginate($this->perPage($request))->withQueryString()
                : null,
            'awaitingPayment' => Order::open()
                ->whereColumn('paid_total', '<', 'total')
                ->with('diningTable')
                ->oldest('opened_at')
                ->get(),
            'lastSession' => $session ? null : CashSession::latest('closed_at')->first(),
            'movementTypes' => CashMovementType::cases(),
        ]);
    }

    public function open(CashRequest $request): RedirectResponse
    {
        try {
            $this->cash->openSession($request->user(), (float) $request->validated('opening_amount'), $request->validated('note'));
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('cash.index')->with('success', 'Kasa açıldı. İyi çalışmalar!');
    }

    public function movement(CashRequest $request): RedirectResponse
    {
        $type = CashMovementType::from($request->validated('type'));

        try {
            $this->cash->addMovement($request->user(), $type, (float) $request->validated('amount'), $request->validated('description'));
        } catch (DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return back()->with('success', "{$type->label()} kaydedildi.");
    }

    public function close(CashRequest $request): RedirectResponse
    {
        try {
            $session = $this->cash->closeSession($request->user(), (float) $request->validated('closing_amount'), $request->validated('note'));
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        $difference = (float) $session->difference;
        $message = $difference == 0.0
            ? 'Kasa kapatıldı; sayım beklenen tutarla birebir tuttu.'
            : 'Kasa kapatıldı; sayımda '.money(abs($difference)).' '.($difference > 0 ? 'fazla' : 'eksik').' var.';

        return redirect()->route('cash.sessions.show', $session)->with($difference == 0.0 ? 'success' : 'error', $message);
    }

    /**
     * Geçmiş kasa oturumları ve seçilen günün kasa raporu (sp_gunluk_kasa_raporu prosedürü).
     */
    public function history(Request $request): View
    {
        $sessions = CashSession::query()
            ->with(['opener', 'closer'])
            ->withCount('payments')
            ->when($this->dateRange($request), fn ($q, array $range) => $q->whereBetween('opened_at', $range))
            ->latest('opened_at')
            ->paginate($this->perPage($request))
            ->withQueryString();

        $reportDate = $this->reportDate($request);

        return view('cash.history', [
            'sessions' => $sessions,
            'reportDate' => $reportDate,
            'report' => collect(DB::select('CALL sp_gunluk_kasa_raporu(?)', [$reportDate->toDateString()]))
                ->map(fn ($row) => [
                    'method' => PaymentMethod::from($row->odeme_yontemi),
                    'count' => (int) $row->odeme_sayisi,
                    'orders' => (int) $row->siparis_sayisi,
                    'total' => (float) $row->toplam_tutar,
                ]),
        ]);
    }

    public function show(CashSession $session): View
    {
        $session->load(['opener', 'closer']);

        return view('cash.show', [
            'session' => $session,
            'summary' => $this->cash->summary($session),
            'movements' => $session->movements()->with(['user', 'payment.order.diningTable'])->latest()->latest('id')->get(),
        ]);
    }

    private function reportDate(Request $request): Carbon
    {
        $range = $this->dateRange($request, 'rapor');

        return $range ? $range[0]->copy()->startOfDay() : today();
    }
}
