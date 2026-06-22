 <div class="step-arrow-nav mt-n3 mx-n3 mb-3">
     <ul class="nav nav-pills nav-justified custom-nav" role="tablist" style="background-color: #2074ba1c !important;">
         <li class="nav-item" role="presentation">
             <a href="/sessions/review/<?= $session->id ?>" class="nav-link fs-15 p-3 <?php if ($tab === 'program_data') : ?> active <?php endif ?>" type="button" role="tab" aria-controls="pills-bill-info" aria-selected="true">
                 <i class="ri-file-list-line fs-16 p-2 bg-primary-subtle text-primary rounded-circle align-middle me-2"></i> Programs
             </a>
         </li>
         <li class="nav-item" role="presentation">
             <a href="/sessions/review/<?= $session->id ?>/mands" class="nav-link fs-15 p-3 <?php if ($tab === 'mands_data') : ?> active <?php endif ?>" type="button" role="tab" aria-controls="pills-bill-address" aria-selected="false">
                 <i class="ri-chat-4-line fs-16 p-2 bg-primary-subtle text-primary rounded-circle align-middle me-2"></i> Mands
             </a>
         </li>
         <li class="nav-item" role="presentation">
             <a href="/sessions/review/<?= $session->id ?>/problemBehavior" class="nav-link fs-15 p-3 <?php if ($tab === 'pb_data') : ?> active <?php endif ?>" type="button" role="tab" aria-controls="pills-payment" aria-selected="false">
                 <i class="ri-alert-line fs-16 p-2 bg-primary-subtle text-primary rounded-circle align-middle me-2"></i> Problem Behavior
             </a>
         </li>
         <li class="nav-item" role="presentation">
             <a href="/sessions/review/<?= $session->id ?>/sessionDuration" class="nav-link fs-15 p-3 <?php if ($tab === 'session_duration') : ?> active <?php endif ?>" type="button" role="tab" aria-controls="pills-payment" aria-selected="false">
                 <i class="ri-time-line fs-16 p-2 bg-primary-subtle text-primary rounded-circle align-middle me-2"></i> Durations
             </a>
         </li>

         <li class="nav-item" role="presentation">
             <a href="/sessions/review/<?= $session->id ?>/processConfirmation" class="nav-link fs-15 p-3 <?php if ($tab === 'process_data') : ?> active <?php endif ?>" type="button" role="tab" aria-controls="pills-finish" aria-selected="false">
                 <i class="ri-check-line fs-16 p-2 bg-primary-subtle text-primary rounded-circle align-middle me-2"></i> Process Session
             </a>
         </li>

     </ul>
 </div>