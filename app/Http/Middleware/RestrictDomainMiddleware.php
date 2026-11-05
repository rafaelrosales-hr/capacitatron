<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictDomainMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->has('email')) {
            $email = $request->input('email');

            if (str_ends_with(strtolower($email), '@hrmedia.com.mx')) {
                return redirect()->back()
                    ->withInput($request->only('email', 'remember'))
                    ->withErrors(['email' => 'El acceso con este dominio de correo no está permitido.']);
            }
        }
        return $next($request);
    }
}
