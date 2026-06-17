@if ($paginator->lastPage() > 1)
<?php
$allowedPages = [
    $paginator->currentPage() - 1,
    $paginator->currentPage(),
    $paginator->currentPage() + 1,
    $paginator->currentPage() + 2,
];
?>

<div class="custom_pagination">
    <nav aria-label="Page navigation example">
        <ul class="pagination">
            <li class="page-item {{ ($paginator->currentPage() == 1) ? ' disabled' : '' }}">
                <a class="page-link button_prev ajax-paginator" href="javascript:void(0)" aria-label="Previous" data-url="{{$paginator->path()}}" data-page="{{$paginator->currentPage() - 1}}">
                    <img src="{{asset('assets/backend/img/pagination-left.svg')}}">
                </a>
            </li>
            <?php
            for ($i = 1; $i <= $paginator->lastPage(); $i++) {

                if (!in_array($i, $allowedPages)) {
                    continue;
                }
            ?>
                <li class="page-item ">
                    <a data-url="{{$paginator->path()}}" data-page="{{$i}}" class="page-link ajax-paginator {{ ($paginator->currentPage() == $i) ? ' active' : '' }}" href="javascript:void(0)">{{ $i }}</a>
                </li>
            <?php
            }
            ?>
            <li class="page-item  {{ ($paginator->lastPage() == $paginator->currentPage()) ? ' disabled' : '' }}">
                <a class="page-link ajax-paginator button_prev" href="javascript:void(0)" aria-label="Next" data-url="{{$paginator->path()}}" data-page="{{$paginator->currentPage()+1}}">
                    <img src="{{asset('assets/backend/img/pagination-right.svg')}}">
                </a>
            </li>
        </ul>
    </nav><br>
</div>

@endif
