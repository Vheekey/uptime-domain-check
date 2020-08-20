@component('mail::message')
# Hi,

This is to inform you that {{$endpoints->uri}} has been back up since {{$endpoints->status->created_at->diffForHumans()}}.

Thanks,<br>
Regards
@endcomponent
