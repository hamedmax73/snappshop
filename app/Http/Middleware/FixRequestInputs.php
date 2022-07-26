<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class FixRequestInputs
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $inputs = $request->all();
        $fixed_persian = $this->removePersianNumbers($inputs);
        $request->replace($fixed_persian);
        return $next($request);
    }

    /**
     * convert arabic/persian numbers into persian
     * @param array $inputs
     * @return array
     */
    protected function removePersianNumbers(array $inputs): array
    {
        foreach ($inputs as $key => $value) {
            if (is_array($value)) {
                $inputs[$key] = $this->removePersianNumbers($value);
            } elseif (! ($value instanceof UploadedFile) && is_string($value)) {
                $inputs[$key] = fa_to_en($value);
            }
        }

        return $inputs;
    }

}
