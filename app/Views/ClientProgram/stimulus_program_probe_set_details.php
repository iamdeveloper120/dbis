 <div class="card border card-border-primary">
     <div class="card-header">
         <h6 class="card-title mb-0"><?= esc($probeSet['name']) ?></h6>
     </div>
     <div class="card-body">
         <p class="card-text"><?= esc($probeSet['description']) ?></p>  
         <?= $inputsHtml ?>
         <div id="inputContainer"></div>
     </div>
 </div>