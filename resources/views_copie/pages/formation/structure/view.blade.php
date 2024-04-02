<div class="accordion  accordion-toggle-arrow" id="accordionHerarchicalStructure">
    <div class="card">
        <div class="card-header">
            <div class="card-title" data-toggle="collapse" data-target="#collapseHerarchicalStructure">
                <i class="flaticon-map"></i> Structure hiérarchique
            </div>
        </div>
        <div id="collapseHerarchicalStructure" class="collapse show" data-parent="#accordionHerarchicalStructure">
            <div class="card-body">
                <input type="hidden" id="product_id" value="{{ $pf_id }}" />
                <div id="structure_tree" class="tree-demo"></div>
            </div>
        </div>
    </div>
</div>

<script>
$('#structure_tree').jstree({
    "core": {
        "multiple": false,
        "themes": {
            "responsive": true
        },
        //"check_callback" : false,
        'data': {
            'url': function(node) {
                return '/get/tree/hierarchical/structure/'+$("#product_id").val();
            },
            'data': function(node) {
                return {
                    'parent': node.id
                };
            }
        },
    },
    "checkbox": {
        "three_state": false, // to avoid that fact that checking a node also check others
        //"whole_node" : false,  // to avoid checking the box just clicking the node 
        //"tie_selection" : true // for checking without selecting and selecting without checking
    },
    "plugins": ["state"]
});
</script>