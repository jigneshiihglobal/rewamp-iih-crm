<a class="d-flex notification_item" href="#">
    <div class="list-item d-flex align-items-start">
        <div class="list-item-body flex-grow-1">
            <p class="media-heading">
                <span class="fw-bolder">{{ $notification->data['title'] ?? '' }}</span>
            </p><small class="notification-text">
                {{ $notification->data['description'] ?? '' }}</small>
        </div>
    </div>
</a>