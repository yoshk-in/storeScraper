<div class="container bg-light p-4">
    {{-- A good traveler has no fixed plans and is not intent upon arriving. --}}
    <h2 class="d-flex justify-content-center text-secondary"> Список магазинов и предлагаемых ими купонов </h2>
    <hr>
    {{-- <button class="btn btn-success m-2" type="button" wire:click="$refresh">Обновить список</button> --}}
    <div wire:loading wire:target="doScrape" class="d-flex align-items-center">
        <strong wire:loading wire:target="doScrape" class="text-warning">Загрузка...</strong>
        <div wire:loading wire:target="doScrape" class="spinner-border ml-auto text-warning" role="status" aria-hidden="true"></div>
    </div>
    <button class="btn btn-success m-2" type="button" wire:click="doScrape" wire:loading.attr="hidden">Запустить скрейпинг</button>
    <div class="row p-3">

        @if ($stores->isEmpty())
        Нет данных
        @else
        <ul class="list-group">
            @foreach ($stores as $index => $store)
            <li class="list-group-item">
                <div>Магазин: </div>
                <h3 class="d-flex justify-content-between"> {{ $store->name }} {{-- <button class="btn text-secondary"
                        type="button">Добавить купон вручную</button> --}}</h3>
                @if ($store->coupons->isEmpty())
                <small>Нет купонов</small>
                @else
                <div class="accordion accordion-flush" id="accordion">
                    <div class="accordion-item">
                        <h2 wire:ignore.self class="accordion-header" id="flush-headingOne">
                            <button class="accordion-button collapsed" data-bs-toggle="collapse"
                                data-bs-target="#store-{{ $store->id }}" aria-expanded="false"
                                aria-controls="flush-collapseOne">
                                <small>Купоны: </small>
                            </button>
                        </h2>
                        <div wire:ignore.self id="store-{{ $store->id }}" class="accordion-collapse collapse"
                            aria-labelledby="flush-headingOne" data-bs-parent="#accordion">
                            <div class="accordion-body">
                                <ul>
                                    @foreach ($store->coupons as $coupon)
                                    <li>
                                        <h6><strong> {{ $coupon->header }} </strong></h6>
                                        <div> {{ $coupon->image }} </div>
                                        <div> {{ $coupon->body }} </div>
                                        <div> {{ $coupon->expire }}</div>
                                    </li>
                                    @endforeach
                                </ul>
                                <hr />
                                <div wire:ignore>
                                    <h5 class="text-secondary">Добавить купон вручную</h5>
                                    <div wire:key="index-{{ $index }}">
                                        <input class="form-control mb-2" wire:model.change="header.{{ $index }}"
                                            placeholder="заголовок">
                                        <input class="form-control mb-2" wire:model.change="image.{{ $index }}"
                                            placeholder="описание">
                                        <div class="d-flex flex-row-reverse">
                                            <button type="button" class="btn btn-outline-primary"
                                                wire:click="addCoupon({{ $index }})">Добавить</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </li>
            @endforeach
        </ul>

        @endif
    </div>
</div>