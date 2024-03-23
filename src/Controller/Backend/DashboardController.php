<?php

namespace App\Controller\Backend;

use App\Repository\OrderRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

#[Route('/admin', name: 'admin')]
class DashboardController extends AbstractController
{
    public function __construct(
        private readonly ChartBuilderInterface $chartBuilder,
        private readonly OrderRepository $orderRepository
    ) {
    }

    #[Route('', '.dashboard', methods: ['GET'])]
    public function index(
        UserRepository $userRepository,

    ) {
        return $this->render('Backend/dashboard.html.twig', [
            'numberUsers' => $userRepository->countAllCustomer(),
            'caThisMonth' => $this->orderRepository->getCaThisMonth(),
            'caThisYear' => $this->orderRepository->getCaThisYear(),
            'averageCart' => $this->orderRepository->getAverageCart(),
            'chartCA' => $this->getChartCa(),
            'chartProducts' => $this->getChartProduct(),
        ]);
    }

    private function getChartCa(): Chart
    {
        $chart = $this->chartBuilder->createChart(Chart::TYPE_BAR);

        foreach ($this->getDataChatCa() as $data) {
            $labels[] = $data['labels'];
            $datasets[] = $data['datasets'];
        }

        $chart->setData([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'CA par mois',
                    'backgroundColor' => '#f8c557',
                    'borderColor' => 'rgb(255, 99, 132)',
                    'borderRadius' => 8,
                    'data' => $datasets,
                ],
            ],
        ]);

        $chart->setOptions([
            'responsive' => true,
            'scales' => [
                'x' => [
                    'stacked' => true,
                    'border' => [
                        'dislay' => false,
                    ],
                ],
                'y' => [
                    'stacked' => true,
                    'suggestedMin' => 0,
                    'suggestedMax' => 1000,
                ],
            ],
            'plugins' => [
                'zoom' => [
                    'zoom' => [
                        'wheel' => [
                            'enabled' => true,
                            'speed' => 0.01,
                        ],
                        'pinch' => ['enabled' => true],
                        'mode' => 'x',
                        'drag' => [
                            'enabled' => true,
                        ],
                    ],
                ],
                'colors' => [
                    'enabled' => false,
                ]
            ]
        ]);

        // dd($chart);
        return $chart;
    }

    private function getChartProduct(): Chart
    {
        $chart = $this->chartBuilder->createChart(Chart::TYPE_BAR);

        $labels = [];
        $datasets = [];

        foreach ($this->orderRepository->findAmountTTCByProduct(5) as $product) {
            $labels[] = $product['label'];
            $datasets[] = $product['data'];
        }

        $chart
            ->setData([
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'CA par produit',
                        'backgroundColor' => '#f8c557',
                        'borderColor' => 'rgb(255, 99, 132)',
                        'borderRadius' => 8,
                        'data' => $datasets,
                    ]
                ],
            ])
            ->setOptions([
                'indexAxis' => 'y',
                'responsive' => true,
                'scales' => [
                    'x' => [
                        'stacked' => true,
                    ],
                    'y' => [
                        'stacked' => true,
                        'border' => [
                            'dislay' => false,
                        ],
                    ],
                ],
            ]);

        return $chart;
    }

    private function getDataChatCa(): array
    {
        $latestDate = (new \DateTime('first day of next month'))->modify('-12 month');

        $data = [];
        $formatter = new \IntlDateFormatter('fr_FR', \IntlDateFormatter::LONG, \IntlDateFormatter::NONE);

        for ($i = 1; $i <= 12; ++$i) {
            $data[] = [
                'labels' => ucfirst(mb_substr($formatter->format($latestDate), 2)),
                'datasets' => $this->orderRepository->getCAByMonth($latestDate),
            ];
            $latestDate = $latestDate->modify('first day of next month');
        }

        return $data;
    }
}
