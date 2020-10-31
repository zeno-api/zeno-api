<?php

declare(strict_types=1);

namespace App\Presenter;

use Illuminate\Http\Request;
use Zeno\Http\Presenter\Presenter;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
class WelcomePresenter
{
    private Presenter $presenter;

    public function __construct(Presenter $presenter)
    {
        $this->presenter = $presenter;
    }

    public function __invoke(Request $request)
    {
        return $this->presenter->render($request, 200, [
            'engine' => sprintf('Zeno/%s', config('app.version')),
        ]);
    }
}
