@component('mail::message')
# Hi,

This is to inform you that {{$endpoints->uri}} has been down {{$endpoints->status->created_at->diffForHumans()}}.

Thanks,<br>
Regards
@endcomponent
