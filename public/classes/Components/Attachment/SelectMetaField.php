<?php


namespace Palasthotel\WordPress\ProLitteris\Components\Attachment;



use Palasthotel\WordPress\ProLitteris\Components\Model\Option;
use Palasthotel\WordPress\ProLitteris\Components\Service\ProviderInterface;

/**
 * Class SelectMetaField
 * @version 0.1.1
 */
class SelectMetaField extends MetaField {

	/**
	 * @var Option[]
	 */
	private $options = [];

	/**
	 * @param Option[]|ProviderInterface $options
	 *
	 * @return $this
	 */
	public function options( $options ): self {
		$this->options = $options;

		return $this;
	}

	protected function field( array $field, \WP_Post $post ): array {
		$field = parent::field( $field, $post );

		$field["input"] = "html";
		$name = $this->getFormName($post->ID);
		$value = $this->getValue($post->ID);

		$options = $this->options instanceof ProviderInterface ? $this->options->get() : $this->options;

		ob_start();
		echo "<select name='" . esc_attr( $name ) . "' id='" . esc_attr( "attachments-{$post->ID}-{$this->id}" ) . "' style='max-width: 100%'>";
		foreach ($options as $option){
            echo "<option value='" . esc_attr( $option->value ) . "' " . selected( $value === $option->value, true, false ) . ">" . esc_html( $option->label ) . "</option>";
        }
		echo "</select>";
		$field["html"] = ob_get_contents();
		ob_end_clean();

		return $field;
	}


}
