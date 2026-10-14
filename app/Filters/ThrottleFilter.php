<?php

declare(strict_types=1);

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

final class ThrottleFilter implements FilterInterface
{
    private const MAX_REQUESTS = 60;
    private const WINDOW_SECONDS = 60;

    public function before(RequestInterface $request, $arguments = null)
    {
        $cache = service('cache');
        $ip    = $request->getIPAddress();
        $path  = $request->getUri()->getPath();

        $key  = 'throttle_' . md5($ip . '_' . $path);
        $now  = time();
        $data = $cache->get($key);

        if (! is_array($data) || (int) ($data['reset_at'] ?? 0) <= $now) {
            $cache->save($key, [
                'count'    => 1,
                'reset_at' => $now + self::WINDOW_SECONDS,
            ], self::WINDOW_SECONDS);

            return null;
        }

        if ((int) $data['count'] >= self::MAX_REQUESTS) {
            return service('response')
                ->setStatusCode(429)
                ->setJSON([
                    'status'  => 'error',
                    'message' => 'Terlalu banyak permintaan. Silakan tunggu beberapa saat.',
                ]);
        }

        $data['count'] = (int) $data['count'] + 1;
        $sisaTtl       = max(1, (int) $data['reset_at'] - $now);
        $cache->save($key, $data, $sisaTtl);

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
