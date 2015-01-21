<?php
class Event {
	const CAPTURING_PHASE = 1;
	const AT_TARGET = 2;
	const BUBBLING_PHASE = 3;

	private $mBubbles;
	private $mCancelable;
	private $mDefaultPrevented;
	private $mEventPhase;
	private $mImmediatePropagationStopped;
	private $mIsTrusted;
	private $mNodeStack;
	private $mPropagationStopped;
	private $mTarget;
	private $mTimeStamp;
	private $mType;

	public function __construct($aEventName) {
		$this->initEvent();
		$this->mDefaultPrevented = false;
		$this->mEventPhase = self::CAPTURING_PHASE;
		$this->mImmediatePropagationStopped = false;
		$this->mIsTrusted = false;
		$this->mNodeStack = new SplStack();
		$this->mPropagationStopped = false;
		$this->mTimeStamp = time();
		$this->mType = $aEventName;
	}

	public function __get($aName) {
		switch ($aName) {
			case 'bubbles':
				return $this->mBubbles;
			case 'cancelable':
				return $this->mCancelable;
			case 'eventPhase':
				return $this->mEventPhase;
			case 'timeStamp':
				return $this->mTimeStamp;
			case 'defaultPrevented':
				return $this->mDefaultPrevented;
			case 'isTrusted':
				return $this->mIsTrusted;
			case 'target':
				return $this->mTarget;
			case 'type':
				return $this->mType;
		}
	}

	public function initEvent($aBubbles = true, $aCancelable = true) {
		$this->mBubbles = $aBubbles;
		$this->mCancelable = $aCancelable;
	}

	public function preventDefault() {
		$this->mDefaultPrevented = true;
	}

	public function stopPropagation() {
		$this->mPropagationStopped = true;
	}

	public function stopImmediatePropagation() {
		$this->mPropagationStopped = true;
		$this->mImmediatePropagationStopped = true;
	}

	public function _getNodeStack() {
		if ($this->mNodeStack->isEmpty()) {
			$node = $this->mTarget;

			while ($node->parentNode) {
				$this->mNodeStack[] = $node;
				$node =& $node->parentNode;
			}

		}

		return $this->mNodeStack;
	}

	public function _isPropagationStopped() {
		return $this->mPropagationStopped;
	}

	public function _isImmediatePropagationStopped() {
		return $this->mImmediatePropagationStopped;
	}

	public function _setTarget(Node &$aTarget) {
		$this->mTarget = $aTarget;
	}

	public function _updateEventPhase($aPhase) {
		$this->mEventPhase = $aPhase;
	}
}

class CustomEvent extends Event {
	private $mDetail;

	public function __construct($aEventName, CustomEventInit &$aEventInitDict = null) {
		parent::__construct($aEventName);

		if (is_null($aEventInitDict)) {
			$aEventInitDict = new CustomEventInit();
		}

		$this->initCustomEvent($aEventInitDict->bubbles, $aEventInitDict->cancelable, $aEventInitDict->detail);
	}

	public function __get($aName) {
		switch ($aName) {
			case 'detail':
				return $this->mDetail;
			default:
				return parent::__get($aName);
		}
	}

	public function initCustomEvent($aBubbles = true, $aCancelable = true, &$aDetail = null) {
		$this->initEvent($aBubbles, $aCancelable);
		$this->mDetail =& $aDetail;
	}
}

class CustomEventInit {
	public $bubbles;
	public $cancelable;
	public $detail;

	public function __construct() {
		$this->bubbles = true;
		$this->cancelable = true;
		$this->detail = null;
	}
}