<?php
# start use
use function lray138\G2\dump;
use lray138\G2\{Str, Kvm};
# end use

return function($data = []) { 
	# start data processing
    $data_kvm = Kvm::of($data);
    extract($data);

    $container_class = $container_class ?? 'container';

    $section_attributes = $data_kvm
        ->tryProp('attrs')
        ->map(function ($a) {
            $kvm = $a instanceof Kvm
                ? $a
                : Kvm::of(is_array($a) ? $a : []);

            return Str::of(kvm_attrs_reduce_to_string($kvm));
        })
        ->getOrElse(Str::of(''));

    $col_1_class_add = '';
    if (isset($wrap_attrs['col_1']['class_add'])) {
        $col_1_class_add = (string) $wrap_attrs['col_1']['class_add'];
    }
    # end data processing
	return "<section {$section_attributes}><div class=\"{$container_class}\"><div class=\"row\"><div class=\"col-12 {$col_1_class_add}\">{$content}</div></div></div></section><!-- dist:
/Users/lray/Sites/babygirlatl/wp-content/themes/blueprint/partials//wraps/section-one-col.php
-->";
};

# src: webpack/src/blueprint/partials/wraps/section-one-col.ejs